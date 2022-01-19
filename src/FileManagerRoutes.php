<?php

use FileManager\Support\FileManagerController;
use Illuminate\Support\Facades\Route;

$name = config('filemanager.name' , 'laravel-file-manager');
$middleware = config('filemanager.middleware' , 'auth');
Route::prefix($name)->name('laravel-file-manager.')->middleware($middleware)->group(function (){
    Route::post('setup' , [FileManagerController::class , 'setup'])->name('setup');
    Route::post('delete-images' , [FileManagerController::class , 'deleteImages'])->name('delete-images');
    Route::post('delete-folder' , [FileManagerController::class , 'deleteFolder'])->name('delete-folder');
    Route::post('create-folder' , [FileManagerController::class , 'createFolder'])->name('create-folder');
    Route::post('upload-images' , [FileManagerController::class , 'uploadImages'])->name('upload-images');
});
