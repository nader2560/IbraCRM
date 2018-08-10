<?php

namespace App;

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
