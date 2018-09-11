<?php 

return [
    
    'boolean' => [
        '0' => 'No',
        '1' => 'Yes',
    ],

    'role' => [
        '0' => 'User',
        '10' => 'Admin',
    ],
    
    'status' => [
        '1' => 'Active',
        '0' => 'Inactive',
    ],

    'avatar' => [
        'public' => '/storage/avatar/',
        'folder' => 'avatar',
        
        'width'  => 400,
        'height' => 400,
    ],

    'product_picture' => [
        'public' => '/storage/product_image/',
        'folder' => 'product_image',

        'width'  => 400,
        'height' => 400,
    ],

    'product_thumbnail' => [
        'public' => '/storage/product_thumbnail/',
        'folder' => 'product_thumbnail',

        'width'  => 160,
        'height' => 160,
    ],

    /*
    |------------------------------------------------------------------------------------
    | ENV of APP
    |------------------------------------------------------------------------------------
    */
    'APP_ADMIN' => 'admin',
    'APP_TOKEN' => env('APP_TOKEN', 'admin123456'),
];
