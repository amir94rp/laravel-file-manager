## About Laravel File Manager

this package developed for vue 3 and has the basic requirements of a file manager ( image uploader ). 


### Install



```php
composer require phoneauth/breeze --dev
```

After Composer has installed the PhoneAuth package, you may run the phoneauth:install Artisan command. This command publishes the authentication views, routes, controllers, and other resources to your application.

```php
php artisan phoneauth:install

npm install
npm run dev
php artisan migrate
```

### SMS Channel

This package uses a custom channel to send SMS and you have to make changes in the following file for your own use

```php 
\App\Notifications\VerifyPhoneNumber
```

## License

The Laravel package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
