<?php

namespace FileManager\Support;

use Illuminate\Support\ServiceProvider;

class FileManagerProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/FileManagerConfig.php' => config_path('filemanager.php'),
        ] , 'laravel-file-manager');

        $this->loadRoutesFrom(__DIR__.'/FileManagerRoutes.php');
    }
}
