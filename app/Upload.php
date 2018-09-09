<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Upload extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'image_path', 'thumbnail_path'
    ];

    /*
    |------------------------------------------------------------------------------------
    | Validations
    |------------------------------------------------------------------------------------
    */
    public static function rules($update = false, $id = null)
    {
        $commun = [
            'email'    => "required|email|unique:users,email,$id",
            'password' => 'nullable|confirmed',
            'avatar' => 'image',
        ];

        if ($update) {
            return $commun;
        }

        return array_merge($commun, [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */
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
