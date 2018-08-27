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
use Sonnenglas\AmazonMws\AmazonFeed;

class Product extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'price', 'image_path', 'thumbnail_path'
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
            'image_path'  => "required|image",
        ];

        return $commun;
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
        $productFeedXml->Message->Product->LaunchDate = $product->created_at->toDateTimeString();
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
        $imageFeedXml->Message->ProductImage->SKU = $product->sku;
        $imageFeedXml->Message->ProductImage->ImageLocation = $product->image_path;


        // Getting the response for each one of the feeds
        $product_response = Product::submitAmazonFeed("store1", "_POST_PRODUCT_DATA_", $productFeedXml);
        $inventory_response = Product::submitAmazonFeed("store1", "_POST_INVENTORY_AVAILABILITY_DATA_", $inventoryFeedXml);
        $price_response = Product::submitAmazonFeed("store1", "_POST_PRODUCT_PRICING_DATA_", $priceFeedXml);
        $image_response = Product::submitAmazonFeed("store1", "_POST_PRODUCT_IMAGE_DATA_", $imageFeedXml);

        dd($product_response);
    }

    private static function submitAmazonFeed ($store, $feed_type, $xml){
        $amazon_feed = new AmazonFeed($store);
        $amazon_feed->setFeedType($feed_type);
        $amazon_feed->setFeedContent($xml->asXML());
        $amazon_feed->submitFeed();

        return $amazon_feed->getResponse();
    }

    /**
     * todo review the post because its shit (pass only product id as it is a static func)
     * @param $request : contains the inputs' values (array)
     * @param $post_id : the product's id (used to make the post GUID)
     * @return mixed : id of the wp post
     */

    public static function createWordpressPost($request, $post_id = null){
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
        if($post_id && env("WP_GUID_BASE")){
            $post_guid = env("WP_GUID_BASE") . $post_id;
        } else {
            $post_guid = "";
        }
        $postData = array(
            "post_author" => env("WP_ROBOT_ID", 1),
            "post_date" => Carbon::now()->toDateTimeString(),
            "post_date_gmt" => Carbon::now()->tz("UTC")->toDateTimeString(),
            "post_content" => $request["description"]."<br/> Price is : ".$request["price"],
            "post_title" => $request["title"],
            "post_excerpt" => "",
            "post_guid" => $post_guid,
            "post_mime_type" => "",
            "to_ping" => "",
            "pinged" => "",
            "post_content_filtered" => "",

        );

        $post = Post::create($postData);

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
        $item->SKU = 'ABC-001'; // todo
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
        $item->PictureDetails->PictureURL = [url($product->image_path)];
        /**
         * List item in the op by category
         * Decorating Tools for Cake Decorating > Cake Boards (183325) category.
         */
        $item->PrimaryCategory = new CategoryType();
        $item->PrimaryCategory->CategoryID = env("EBAY_CATEGORY_ID", "183325");
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
            'VisaMC',
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
        if ($response->Ack !== 'Failure') {
            dd($response);
            /*printf(
                "The item was listed to the eBay Sandbox with the Item number %s\n",
                $response->ItemID
            );*/
        }
        dd("The End");

        // *********************** OLD CODE ***********************
        // Create the request object.
        $request = new CreateOrReplaceInventoryItemRestRequest();

        // $request->sku = '123'; // SKU goes for (Stock-Keeping Unit)

        $request->availability = new Availability();
        $request->availability->shipToLocationAvailability = new ShipToLocationAvailability();
        $request->availability->shipToLocationAvailability->quantity = 50; // todo have a default quantity as env variable ?

        $request->condition = ConditionEnum::C_NEW_OTHER;

        $request->product = new \DTS\eBaySDK\Inventory\Types\Product(); // Ebay's product
        $request->product->title = $product->title;
        $request->product->description = $product->description;
        /*
         * $request->product->aspects = [
                'Brand'                => ['GoPro'],
                'Type'                 => ['Helmet/Action'],
                'Storage Type'         => ['Removable'],
                'Recording Definition' => ['High Definition'],
                'Media Format'         => ['Flash Drive (SSD)'],
                'Optical Zoom'         => ['10x', '8x', '4x']
            ];
            Aspects are specified as an associative array.
         */
        $request->product->imageUrls = [
            $product->image_path
        ];

        // Send the request
        $response = $service->createOrReplaceInventoryItem($request);
        if (isset($response->errors)) {
            foreach ($response->errors as $error) {
                Log::error(
                    "%s: %s\n%s\n\n",
                    $error->errorId,
                    $error->message,
                    $error->longMessage
                );
            }
        }
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
            dd($response);
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

        return config('variables.product_picture.public').$value;
    }

    public function setImagePathAttribute($photo)
    {
        $this->attributes['image_path'] = move_file($photo, 'product_picture');
        $this->setThumbnailPathAttribute($photo);
    }

    public function getThumbnailPathAttribute($value)
    {
        if (!$value) {
            return 'http://placehold.it/160x160';
        }
        return config('variables.product_thumbnail.public').$value;
    }
    public function setThumbnailPathAttribute($photo)
    {
        $this->attributes['thumbnail_path'] = move_file($photo, 'product_thumbnail');
    }
}
