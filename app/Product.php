<?php

namespace App;

use Carbon\Carbon;
use Corcel\Model\Post;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Image;

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

    /**
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
