## About Laravel File Manager

this package developed for vue 3 and has the basic requirements of a file manager ( image uploader ). 

### Install

```php
composer require amir94rp/laravel-file-manager
```

and now you may publish config file by running the following command

```php
php artisan vendor:publish --tag=laravel-file-manager
```

add @routes to head of your root template.

```blade
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    ...
    @routes
  </head>
  <body>
    ...
  </body>
</html>
```

####vue 3 components

you can use both file-input and file-manager components. install them first.

```javascript 
npm i @amir94rp/vue3-file-input --save-dev
npm i @amir94rp/vue3-file-manager --save-dev
```

#####FileInput

```vue
<FileInput 
    :images="[]" 
    v-on:update:images="images = $event" 
    :sm-cols="1" 
    :md-cols="4" 
    :multiple="false"
/>
```

|                  |Type                           |Description                            |
|------------------|-------------------------------|---------------------------------------|
|images            |Array                          |array of image urls                    |
|multiple          |Boolean                        |allow multiple image selection         |
|sm-cols & md-cols |Numeric (1-12)                 |it defines the maximum columns allowed |

#####FileManager

```vue
<FileManager 
    :open-file-manager="open" 
    :multiple="false"
    v-on:update:openFileManager="open = $event" 
    v-on:update:selectedImages="addImage($event)"
/>
```

|                      |Type                           |Description                            |
|----------------------|-------------------------------|---------------------------------------|
|open-file-manager     |Boolean                        |show file manager modal                |
|multiple              |Boolean                        |allow multiple image selection         |
|update:selectedImages |Array                          |it will return array of selected images|

## License

The Laravel package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
