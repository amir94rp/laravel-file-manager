<?php

return [

    /*
       |--------------------------------------------------------------------------
       | File Manager Name
       |--------------------------------------------------------------------------
       |
       | This value is the name of your file manager.
       | This value is used for:
       | route prefix
       | file manager folder name
       */
    'name'      =>  'laravel-file-manager',

    /*
       |--------------------------------------------------------------------------
       | Image Sizes
       |--------------------------------------------------------------------------
       |
       | File manager will generate multiple sizes of your images
       | and you can define their width here
       |
       */
    'images'    => [
        'lg'    => 500 ,
        'md'    => 250 ,
        'sm'    => 100 ,
    ],

    /*
       |--------------------------------------------------------------------------
       | File Manager Middleware
       |--------------------------------------------------------------------------
       |
       */
    'middleware'    => 'auth'
];
