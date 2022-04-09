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
    'name'      =>  [
        'prefix'    => 'laravel-file-manager',
        'folder'    => 'images'
    ],

    /*
       |--------------------------------------------------------------------------
       | File Manager Middleware
       |--------------------------------------------------------------------------
       |
       */
    'middleware'    => ['web','auth']
];
