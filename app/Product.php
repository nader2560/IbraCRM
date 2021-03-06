<?php

namespace App;

use Carbon\Carbon;
use Corcel\Model\Post;
use DTS\eBaySDK\Inventory\Enums\ConditionEnum;
use DTS\eBaySDK\Inventory\Types\Availability;
use DTS\eBaySDK\Inventory\Types\CreateOrReplaceInventoryItemRestRequest;
use DTS\eBaySDK\Inventory\Types\ShipToLocationAvailability;
use DTS\eBaySDK\Trading\Enums\GalleryTypeCodeType;
use DTS\eBaySDK\Trading\Enums\ListingDurationCodeType;
use DTS\eBaySDK\Trading\Enums\ListingTypeCodeType;
use DTS\eBaySDK\Trading\Enums\SeverityCodeType;
use DTS\eBaySDK\Trading\Enums\ShippingTypeCodeType;
use DTS\eBaySDK\Trading\Types\AddFixedPriceItemRequestType;
use DTS\eBaySDK\Trading\Types\AddItemRequestType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\BestOfferDetailsType;
use DTS\eBaySDK\Trading\Types\CategoryType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\ListingDetailsType;
use DTS\eBaySDK\Trading\Types\PictureDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingServiceOptionsType;
use Hkonnet\LaravelEbay\EbayServices;
use Hkonnet\LaravelEbay\Facade\Ebay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Image;
use Pixelpeter\Woocommerce\Facades\Woocommerce;
use Sonnenglas\AmazonMws\AmazonFeed;
use Sonnenglas\AmazonMws\AmazonFeedResult;

class Product extends Model
{
    const standard_product_id_types = ["0" => "UPC", "1" => "EAN", "2" => "ISBN"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'standard_product_category','standard_product_id_type', 'standard_product_id_code', 'price', 'image_path', 'thumbnail_path'
    ];

    /*
    |------------------------------------------------------------------------------------
    | Validations
    |------------------------------------------------------------------------------------
    */
    public static function rules()
    {
        $commun = [
            'title'    => "required",
            'price'    => "required|numeric|max:99999",
            'standard_product_id_code' => 'required',
            'standard_product_category' => 'required',
            'image_path'  => "required",
            'image_path.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
        ];

        return $commun;
    }

    /*
    |------------------------------------------------------------------------------------
    | Relationship
    |------------------------------------------------------------------------------------
    */
    public function uploads(){
        return $this->hasMany('App\Upload', 'product_id');
    }

    /*
    |------------------------------------------------------------------------------------
    | API Integrations
    |------------------------------------------------------------------------------------
    */

    public static function createAmazonPost($product_id) {
        $product = Product::findOrFail($product_id);

        // Product Feed
        $productFeedXml = simplexml_load_file(public_path("amazon-xml/product.xml"));
        $productFeedXml->Message->Product->SKU = $product->sku;
        $productFeedXml->Message->Product->StandardProductID->Type = Product::standard_product_id_types[(int) $product->standard_product_id_type];
        $productFeedXml->Message->Product->StandardProductID->Value = $product->standard_product_id_code;
        $productFeedXml->Message->Product->LaunchDate = $product->created_at->addDay()->toIso8601ZuluString();
        $productFeedXml->Message->Product->DescriptionData->Title = $product->title;
        $productFeedXml->Message->Product->DescriptionData->Description = $product->description;
        // Inventory Feed
        $inventoryFeedXml = simplexml_load_file(public_path("amazon-xml/inventory.xml"));
        $inventoryFeedXml->Message->Inventory->SKU = $product->sku;
        // Price Feed
        $priceFeedXml = simplexml_load_file(public_path("amazon-xml/price.xml"));
        $priceFeedXml->Message->Price->SKU = $product->sku;
        $priceFeedXml->Message->Price->StandardPrice = $product->price;
        // Image Feed
        $imageFeedXml = simplexml_load_file(public_path("amazon-xml/image.xml"));
        // Making the Image Feed a bit better :)
        $i=0;
        foreach($product->uploads as $upload){
            if($i == 0)
                $image_type = "Main";
            else
                $image_type = "PT".strval($i+1);

            $imageFeedXml->addChild("Message");
            $lastMessage = $imageFeedXml->Message[$i];
            $lastMessage->addChild("MessageID",$i+1);
            $lastMessage->addChild("OperationType", 'Update');
            $lastMessage->addChild("ProductImage");
            $productImage = $lastMessage->ProductImage;
            $productImage->addChild("SKU",$product->sku);
            $productImage->addChild("ImageType",$image_type);
            $productImage->addChild("ImageLocation",url($upload->image_path));
            $i++;
        }

        // Getting the response for each one of the feeds
        $product_response = Product::submitAmazonFeed("store1", "_POST_PRODUCT_DATA_", $productFeedXml);
        $inventory_response = Product::submitAmazonFeed("store1", "_POST_INVENTORY_AVAILABILITY_DATA_", $inventoryFeedXml);
        $price_response = Product::submitAmazonFeed("store1", "_POST_PRODUCT_PRICING_DATA_", $priceFeedXml);
        $image_response = Product::submitAmazonFeed("store1", "_POST_PRODUCT_IMAGE_DATA_", $imageFeedXml);

        return [
            $product_response['FeedSubmissionId'],
            $inventory_response['FeedSubmissionId'],
            $price_response['FeedSubmissionId'],
            $image_response['FeedSubmissionId']
        ];
    }

    private static function submitAmazonFeed ($store, $feed_type, $xml){
        $amazon_feed = new AmazonFeed($store);
        $amazon_feed->setFeedType($feed_type);
        $amazon_feed->setFeedContent($xml->asXML());
        $amazon_feed->submitFeed();

        return $amazon_feed->getResponse();
    }

    public static function createGumtreePost($product_id){
        $product = Product::findOrFail($product_id);

        $string = config("gumtree.string");
        $url= Product::getGumtreeUrl($string);
        Product::gumtreePrepare($url,$string);
        $checkout=Product::gumtreePostItem($url,$string);
        Product::gumtreeCheckoutItem($checkout,$string);
    }

    private static function getGumtreeUrl($cookie){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://my.gumtree.com/postad");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Dnt: 1";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "X-Hola-Request-Id: 97040";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = "Referer: https://my.gumtree.com/postad/";
        $headers[] = $cookie;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 97040: before request, send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $header = curl_exec($ch);
        $info  = curl_getinfo($ch);
        curl_close($ch);
        return $info["redirect_url"];
    }

    private static function gumtreePrepare($url, $cookie){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, -1);
        $tt = new \Datetime();
        $TITLE="TIME:".$tt->format('Y-m-d H:i:s');
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"formErrors":{},"categoryId":"4681","locationId":"203","postcode":"TW91EL","visibleOnMap":"true","area":null,"termsAgreed":"","title":"'.$TITLE.'","description":"Plusieurs variations de Lorem Ipsum peuvent \EAtre trouv\E9es ici ou l\E0, mais la majeure partie dentre elles a \E9t\E9aaa alt\E9r\E9e","previousContactName":null,"contactName":"Nejmeddine","previousContactEmail":null,"contactEmail":"nejmeddine.khechine@gmail.com","contactTelephone":null,"contactUrl":null,"useEmail":"true","usePhone":"false","useUrl":false,"checkoutVariationId":null,"mainImageId":"0","imageIds":["1098961258","0"],"youtubeLink":"","websiteUrl":"http://","firstName":null,"lastName":null,"emailAddress":"nejmeddine.khechine@gmail.com","telephoneNumber":null,"password":null,"optInMarketing":true,"vrmStatus":"VRM_NONE","attributes":{"price":"11"},"features":{"URGENT":{"productName":"URGENT"},"WEBSITE_URL":{"productName":"WEBSITE_URL","selected":"false"},"FEATURED":{"productName":"FEATURE_7_DAY"},"SPOTLIGHT":{"productName":"HOMEPAGE_SPOTLIGHT"}},"removeDraft":"false","submitForm":true}');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Origin: https://my.gumtree.com";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "X-Hola-Request-Id: 77650";
        $headers[] = "X-Requested-With: XMLHttpRequest";
        $headers[] = $cookie;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Distil-Ajax: fcfxdfwcwavvtvzewaafsewarbtsfcvq";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Content-Type: application/json; charset=UTF-8";
        $headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
        $headers[] = "Referer: https://my.gumtree.com/postad";
        $headers[] = "Dnt: 1";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 77650: before request, send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
    }

    private static function gumtreePostItem($url, $cookie){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url."/bumpup");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, -1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Origin: https://my.gumtree.com";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "X-Hola-Request-Id: 77650";
        $headers[] = "X-Requested-With: XMLHttpRequest";
        $headers[] = $cookie;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Distil-Ajax: fcfxdfwcwavvtvzewaafsewarbtsfcvq";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Content-Type: application/json; charset=UTF-8";
        $headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
        $headers[] = "Referer: https://my.gumtree.com/postad";
        //$headers[] = "Dnt: 1";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 77650: before request, send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $info  = curl_getinfo($ch);
        curl_close ($ch);
        return  $info['redirect_url'];
    }

    private static function gumtreeCheckoutItem($url, $cookie){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Dnt: 1";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "X-Hola-Request-Id: 172411";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = "Referer: https://my.gumtree.com/postad/";
        $headers[] = $cookie;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 172411: before request, send headers, headers received, status: HTTP/1.1 303 See Other, before request ".$url.", send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        curl_close ($ch);
    }


    /**
     * @param $product_id : the product's id (used to make the post GUID)
     * @return mixed : id of the wp post
     */

    public static function createWordpressPost($product_id){
        /* List of attributes of a Wordpress Post :
         *  post_author (default 0)
            post_date (default 0000000)
            post_date_gmt (default 0000000)
            post_content (default None)
            post_title (default None)
            post_excerpt (default None)
            post_status (default publish)
            comment_status (default open)
            ping_status (default open)
            post_password
            post_name
            to_ping (default None )
            pinged (default None )
            post_modified (default 0000000000)
            post_modified_gmt (default 000000000)
            post_content_filtered (default None)
            post_parent (default 0)
            post_guid
            menu_order (default 0)
            post_type (default post)
            post_mime_type
            comment_count (default 0)
         */
        $product = Product::findOrFail($product_id);

        if(env("WP_GUID_BASE")){
            $post_guid = env("WP_GUID_BASE") . $product_id;
        } else {
            $post_guid = "";
        }

        $postData = array(
            "post_author" => env("WP_ROBOT_ID", 1),
            "post_date" => Carbon::now()->toDateTimeString(),
            "post_date_gmt" => Carbon::now()->tz("UTC")->toDateTimeString(),
            "post_content" => $product->description."<br/> Price is : ".$product->price,
            "post_title" => $product->title,
            "post_excerpt" => "",
            "post_guid" => $post_guid,
            "post_mime_type" => "",
            "to_ping" => "",
            "pinged" => "",
            "post_content_filtered" => "",
            "post_type"=>"product",

        );

        //$post = Post::create($postData);


        $images = [];
        $position =0;
        foreach($product->uploads as $upload){
            array_push($images, array(
                'src'=> url($upload->image_path),
                'position'=>$position));
            $position++;
        }
        $data = [
            'name' =>  $product->title,
            'type' => 'simple',
            'regular_price' => $product->price,
            'description' => $product->description,
            'categories' => [
                [
                    'id' => $product->category,
                ]
            ],
            'images' => $images
        ];
        dd($data);
        $post = Woocommerce::post('products',$data);
        return $post->id;
    }

    /**
     * @param $product_id
     */
    public static function createEbayPost($product_id){
        $product = Product::findOrFail($product_id);

        // Create the service object.
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        /**
         * Create the request object.
         */
        $request = new AddFixedPriceItemRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = Ebay::getAuthToken();

        /**
         * Begin creating the fixed price item.
         */
        $item = new ItemType();
        /**
         * We want a multiple quantity fixed price listing.
         */
        $item->ListingType = ListingTypeCodeType::C_FIXED_PRICE_ITEM;
        $item->Quantity = (int)env("EBAY_DEFAULT_QUANTITY", 99);
        /**
         * Let the listing be automatically renewed every 30 days until cancelled.
         */
        $item->ListingDuration = ListingDurationCodeType::C_GTC;
        /**
         * Note that we don't have to specify a currency as eBay will use the site id
         */
        $item->StartPrice = new AmountType(['value' => (double)$product->price+0.01]);
        /**
         * Allow buyers to submit a best offer.
         */
        $item->BestOfferDetails = new BestOfferDetailsType();
        $item->BestOfferDetails->BestOfferEnabled = true;
        /**
         * Automatically accept best offers and decline offers lower than price.
         */
        $item->ListingDetails = new ListingDetailsType();
        $item->ListingDetails->BestOfferAutoAcceptPrice = new AmountType(['value' => (double)$product->price]);
        $item->ListingDetails->MinimumBestOfferPrice = new AmountType(['value' => (double)$product->price]);
        /**
         * Provide a title and description and other information such as the item's location.
         * Note that any HTML in the title or description must be converted to HTML entities.
         */
        $item->Title = $product->title;
        $item->Description = $product->description;
        $item->SKU = $product->sku;
        $item->Country = env("EBAY_COUNTRY_CODE");
        $item->Location = env("EBAY_LOCATION");
        $item->PostalCode = env("EBAY_POSTAL_CODE");
        /**
         * This is a required field.
         */
        $item->Currency = env("EBAY_CURRENCY");
        /**
         * Display a picture with the item.
         */
        $item->PictureDetails = new PictureDetailsType();
        $item->PictureDetails->GalleryType = GalleryTypeCodeType::C_GALLERY;
        $picture_urls = [];
        foreach($product->uploads as $upload){
            array_push($picture_urls, url($upload->image_path));
        }
        $item->PictureDetails->PictureURL = $picture_urls;
        /**
         * List item in the op by category
         * Decorating Tools for Cake Decorating > Cake Boards (183325) category.
         */
        $item->PrimaryCategory = new CategoryType();
        $item->PrimaryCategory->CategoryID = Product::categoryTranslator( $product->category, 1 );
        /**
         * Tell buyers what condition the item is in.
         * For the category that we are listing in the value of 1000 is for Brand New.
         */
        $item->ConditionID = 1000;
        /**
         * Buyers can use one of two payment methods when purchasing the item.
         * Visa / Master Card
         * PayPal
         * The item will be dispatched within 1 business days once payment has cleared.
         * Note that you have to provide the PayPal account that the seller will use.
         * This is because a seller may have more than one PayPal account.
         */
        $item->PaymentMethods = [
            'PayPal'
        ];
        $item->PayPalEmailAddress = env("EBAY_PAYPAL_ADDRESS");
        $item->DispatchTimeMax = 1;
        /**
         * Setting up the shipping details.
         * We will use a Flat shipping rate for both domestic and international.
         */
        $item->ShippingDetails = new ShippingDetailsType();
        $item->ShippingDetails->ShippingType = ShippingTypeCodeType::C_FLAT;
        /**
         * Create our first domestic shipping option.
         * Offer the Economy Shipping (1-10 business days) service at $2.00 for the first item.
         * Additional items will be shipped at $1.00.
         */
        $shippingService = new ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'UK_RoyalMailFirstClassStandard';
        $shippingService->FreeShipping = true;
        //$shippingService->ShippingServiceCost = new AmountType(['value' => (double)env("EBAY_SHIPPING_PRICE")]);
        //$shippingService->ShippingServiceAdditionalCost = new AmountType(['value' => (double)env("EBAY_SHIPPING_PRICE_ADDITIONAL_COST")]);
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
        /**
         * Create our second domestic shipping option.
         * Offer the USPS Parcel Select (2-9 business days) at $3.00 for the first item.
         * Additional items will be shipped at $2.00.
         */
        /*$shippingService = new Types\ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'USPSParcel';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 3.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 2.00]);
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;*/
        /**
         * Create our first international shipping option.
         * Offer the USPS First Class Mail International service at $4.00 for the first item.
         * Additional items will be shipped at $3.00.
         * The item can be shipped Worldwide with this service.
         */
        /*$shippingService = new Types\InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'USPSFirstClassMailInternational';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 4.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 3.00]);
        $shippingService->ShipToLocation = ['WorldWide'];
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;*/
        /**
         * Create our second international shipping option.
         * Offer the USPS Priority Mail International (6-10 business days) service at $5.00 for the first item.
         * Additional items will be shipped at $4.00.
         * The item will only be shipped to the following locations with this service.
         * N. and S. America
         * Canada
         * Australia
         * Europe
         * Japan
         */
        /*$shippingService = new Types\InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'USPSPriorityMailInternational';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 5.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 4.00]);
        $shippingService->ShipToLocation = [
            'Americas',
            'CA',
            'AU',
            'Europe',
            'JP'
        ];
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;*/
        /**
         * The return policy.
         * Returns are accepted.
         * A refund will be given as money back.
         * The buyer will have 14 days in which to contact the seller after receiving the item.
         * The buyer will pay the return shipping cost.
         */
        /*$item->ReturnPolicy = new Types\ReturnPolicyType();
        $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
        $item->ReturnPolicy->RefundOption = 'MoneyBack';
        $item->ReturnPolicy->ReturnsWithinOption = 'Days_14';
        $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';*/
        /**
         * Finish the request object.
         */
        $request->Item = $item;
        /**
         * Send the request.
         */
        $response = $service->addFixedPriceItem($request);
        /**
         * Output the result of calling the service operation.
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                if($error->SeverityCode === SeverityCodeType::C_ERROR){
                    Log::error($error->ShortMessage . "\n" . $error->LongMessage);
                } else {
                    Log::warning($error->ShortMessage . "\n" . $error->LongMessage);
                }
            }
        }
        return $response->ItemID;
    }

    public static function categoryTranslator($categoryId, $framework){
        if($framework == 1){

            switch ($categoryId){
                case 335 :
                    return 183324;
                    break;
                case 336 :
                    return 177009;
                    break;
                case 337 :
                    return 177013;
                    break;
                case 338 :
                    return 38174;
                    break;
                case 339 :
                    return 183339;
                    break;
                case 340 :
                    return 183346;
                    break;
                case 341 :
                    return 177009;
                    break;
                case 342 :
                    return 183324;
                    break;
                case 343 :
                    return 14308;
                    break;
            }
        }



    }
    /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */
    public function getShortDescriptionAttribute(){
        if ($this->description == null){
            return "";
        }
        $short_description = strip_tags($this->description);
        if( strlen($short_description) > 100){
            // truncate string
            $stringCut = substr($short_description, 0, 100);
            $endPoint = strrpos($stringCut, ' ');

            //if the string doesn't contain any space then it will cut without word basis.
            $short_description = $endPoint? substr($stringCut, 0, $endPoint):substr($stringCut, 0);
            $short_description .= '... <a href="'. route(ADMIN . '.products.edit', $this->id) .'">Read more</a>';
        }

        return $short_description;
    }

    public function getPrintablePriceAttribute(){
        return $this->price;
    }

    public function getSkuAttribute(){
        $words = explode(" ", $this->title);
        $acronym = "";

        foreach ($words as $w) {
            $acronym .= $w[0];
        }

        return $acronym . '-' . $this->id;
    }

    public function getImagePathAttribute($value)
    {
        if (!$value) {
            return 'http://placehold.it/400x400';
        }

        return $value;
    }

    public function setImagePathAttribute($photos)
    {
        foreach($photos as $photo){
            $upload = new Upload();
            $upload->product_id = $this->id;
            $upload->image_path = $photo;
            $upload->save();
        }
        $this->attributes['image_path'] = $upload->image_path;
        $this->setThumbnailPathAttribute($upload->thumbnail_path);
    }

    public function getThumbnailPathAttribute($value)
    {
        if (!$value) {
            return 'http://placehold.it/160x160';
        }
        return $value;
    }
    public function setThumbnailPathAttribute($thumbnail_path)
    {
        $this->attributes['thumbnail_path'] = $thumbnail_path;
    }

    public function getAmazonFeedStatusAttribute(){
        $amazon_feed_ids = explode(";", $this->amazon_id);

        $list_raw_feeds = [];
        foreach($amazon_feed_ids as $feed_id){
            $amz = new AmazonFeedResult("store1", $feed_id); //feed ID can be quickly set by passing it to the constructor
            //$amz->setFeedId($feed_id); //otherwise, it must be set this way
            $amz->fetchFeedResult();
            $list_raw_feeds += [$amz->getRawFeed()];
        }
        return $list_raw_feeds;
    }
}