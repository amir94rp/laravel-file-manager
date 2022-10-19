
# Laravel, Inertia Vue 3  Image Manager

This package is a simple image manager that allows you to upload your images, specify the name and format of the uploading files, create and delete folder.

In addition to storing the original image, this package stores 3 images in dimensions of 50%, 25% and 10% of the uploaded image.
on selecting the image, you can choose one of these dimensions and specify an alt text.


## Installation

Install ImageManager Laravel package

```php
composer require amir94rp/laravel-file-manager
```

Publish config file

```php
php artisan vendor:publish --tag=laravel-file-manager
```

Link your storage

```php
php artisan storage:link
```

Install ImageManager Vue3 component

```npm
npm i @amir94rp/vue3-file-manager --save-dev
```

If you are using Tailwind V.2, add the module path to the Tailwind settings file (tailwind.config.js).
In this module, we use aspect ratio and forms plugins, and you should add these to the plugins array.

```js
module.exports = [
    //...
    purge: {
    //...
    content: [
        './node_modules/@amir94rp/vue3-file-manager/dist/components/*.js',
    ],
    //...
    //...
},

corePlugins: {
    aspectRatio: false,
},

plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/aspect-ratio')
],
]
```

If you are not interested in the above method or do not use Tailwind V.2, you can import the style file.

```js
import '@amir94rp/vue3-file-manager/dist/components/style.css';
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

