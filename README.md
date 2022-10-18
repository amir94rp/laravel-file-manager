
# Laravel, Inertia Vue 3  Image Manager

This package is a simple image manager that allows you to upload your images, specify the name and format of the uploading files, create and delete folder.

In addition to storing the original image, this package stores 3 images in dimensions of 50%, 25% and 10% of the uploaded image.
on selecting the image, you can choose one of these dimensions and specify an alt text.


## Installation

Install Image Manager

```php
composer require amir94rp/laravel-file-manager
```

publish config file

```php
php artisan vendor:publish --tag=laravel-file-manager
```

Link you storage

```php
php artisan storage:link
```


## Usage/Examples

```vue
<template>
    <ImageManager v-model:open="open" 
                  @output="log"
                  :alt="alt" 
                  :quality="quality" 
                  :multiple="multiple" 
                  :select="select"/>
</template>

<script>
    import ImageManager from "@amir94rp/vue3-file-manager";

    export default {
      data(){
        return{
          open:false,
          multiple:false,
          select:false,
          quality:'xl',
          alt:false,
        }
      },

      components:{
        ImageManager
      },

      methods:{
        log:function (value){
          console.log(value);
      },
    }
</script>
```


## Available Props


| Prop | Default     | Description                |
| :-------- | :------- | :------------------------- |
| `open` | `false` | **Required**. open and close the ImageManager |
| `multiple` | `false` | active multiple image selecting |
| `select` | `false` | active image size selecting |
| `alt` | `false` | active image alt writing |
| `quality` | `sm` | ImageManager preview quality. possible values : 'xl' , 'lg' , 'md' , 'sm' |






## Events


| Event |   Type   | Description                |
| :-------- | :------- | :------------------------- |
| `output` | `String , Array , Object` | ImageMnager selected images output |






## Demo

[Click here to see the demo](https://image-manager.amir94rp.me/)

## License

[MIT](https://choosealicense.com/licenses/mit/)

