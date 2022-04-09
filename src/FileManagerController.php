<?php

namespace FileManager\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as InterventionImage;
use App\Http\Controllers\Controller;

class FileManagerController extends Controller
{
    /**
     * @param Request $request
     * @return bool
     */
    public function createFolder(Request $request){

        $request->validate([
            'name'  => ['required' , 'string'],
            'path'  => ['nullable' , 'string']
        ]);

        $name = $this->slugString($request->input('name'));
        $path = $request->input('path');

        Storage::makeDirectory($this->getStoragePath('xl') . $path . '/' . $name);
        Storage::makeDirectory($this->getStoragePath('lg') . $path . '/' . $name);
        Storage::makeDirectory($this->getStoragePath('md') . $path . '/' . $name);
        Storage::makeDirectory($this->getStoragePath('sm') . $path . '/' . $name);

        return true;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function deleteFolder(Request $request){

        $request->validate([
            'folder'    => ['required' , 'string']
        ]);

        $folderPath = $request->input('folder');

        Storage::deleteDirectory($this->getStoragePath('xl') . $folderPath);
        Storage::deleteDirectory($this->getStoragePath('lg') . $folderPath);
        Storage::deleteDirectory($this->getStoragePath('md') . $folderPath);
        Storage::deleteDirectory($this->getStoragePath('sm') . $folderPath);

        return true;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function deleteImages(Request $request){

        $request->validate([
            'images'    => ['required' , 'array']
        ]);

        $images = $request->input('images');

        foreach ($images as $image){
            Storage::delete($image);
            Storage::delete(Str::replace($this->getStoragePath('xl') , $this->getStoragePath('lg') , $image));
            Storage::delete(Str::replace($this->getStoragePath('xl') , $this->getStoragePath('md') , $image));
            Storage::delete(Str::replace($this->getStoragePath('xl') , $this->getStoragePath('sm') , $image));
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function uploadImages(Request $request){

        $request->validate([
            'images'        => ['required'] ,
            'images.*'      => ['image'] ,
            'path'          => ['nullable' , 'string'],
            'settings'      => ['required']
        ]);

        $images     = $request->file('images');
        $uploadPath = $request->input('path');
        $settings   = json_decode($request->input('settings'));

        foreach ($images as $index=>$image)
        {
            $this->multiSize($image , $uploadPath , $settings[$index]);
        }

        return true;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function setup(Request $request){

        $request->validate([
            'path'  => ['nullable' , 'string']
        ]);

        $directoryPath = $this->getStoragePath('xl') . $request->input('path');

        $directories = Storage::allDirectories($this->getStoragePath('xl'));
        $mutatedDirectories = [];
        foreach ($directories as $index=>$directory){

            $isExpanded = false;
            if (Str::startsWith($directoryPath , $directory)){
                $isExpanded = true;
            }

            $directory = Str::replace($this->getStoragePath('xl') , '' , $directory);
            $info = [
                'id'            => $index ,
                'directory'     => $directory ,
                'path'          => pathinfo($directory) ,
                'isExpanded'    => $isExpanded ,
                'items'         => [] ,
                'parent_id'     => null
            ];

            foreach ($mutatedDirectories as $counter=>$mutatedDirectory){
                if ($mutatedDirectory['directory']  == $info['path']['dirname']){
                    $mutatedDirectories[$counter]['items'][] = $info['id'];
                    $mutatedDirectories[$counter]['isExpandable'] = true;
                    $info['parent_id'] = $mutatedDirectory['id'];
                    break 1;
                }
            }

            $mutatedDirectories[] = $info;
        }

        $files = Storage::files($directoryPath);
        $mutatedFiles = [];
        foreach ($files as $path){
            $file['path']   = $path;
            $file['name']   = pathinfo($path)['basename'];
            $file['url']    = Storage::url($path);
            $file['time']   = Storage::lastModified($path);
            $mutatedFiles[] = $file;
        }

        return [
            'directories'   => $mutatedDirectories ,
            'files'         => $mutatedFiles
        ];
    }

    /**
     * @param $imageFile
     * @param $directory
     * @param $settings
     */
    private function multiSize($imageFile , $directory , $settings){

        $format = $settings->format;
        $name = $this->slugString($settings->name);
        $fileName = $name  . '.' . $format;
        $imageSize = getimagesize($imageFile);

        $lgSize = round($imageSize[0] * 50 / 100);
        $mdSize = round($imageSize[0] * 25 / 100);
        $smSize = round($imageSize[0] * 10 / 100);

        $xlPath = $this->getStoragePath('xl') . $directory . '/';
        $lgPath = $this->getStoragePath('lg') . $directory . '/';
        $mdPath = $this->getStoragePath('md') . $directory . '/';
        $smPath = $this->getStoragePath('sm') . $directory . '/';

        $originalImage = InterventionImage::make($imageFile)->encode($format , 100);
        $savePath = $xlPath . $fileName;
        $counter = 1;
        while (Storage::exists($savePath))
        {
            $fileName = $name . '-' . $counter . '.' . $format;
            $savePath = $xlPath . $fileName;
            $counter ++;
        }

        Storage::put( $savePath  , $originalImage);

        $this->encodeSave($imageFile , $lgSize , $format , $lgPath , $fileName);
        $this->encodeSave($imageFile , $mdSize , $format , $mdPath , $fileName);
        $this->encodeSave($imageFile , $smSize , $format , $smPath , $fileName);
    }

    /**
     * @param $imageFile
     * @param $imageSize
     * @param $imageFormat
     * @param $savePath
     * @param $imageName
     * @throws \Intervention\Image\Exception\NotReadableException
     */
    private function encodeSave($imageFile , $imageSize , $imageFormat , $savePath , $imageName){
        $imageOutput = InterventionImage::make($imageFile)->resize($imageSize , null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode($imageFormat , 100);
        Storage::put($savePath . $imageName , $imageOutput);
    }

    private function slugString($string)
    {
        return preg_replace('/\s+/', '-', trim($string));
    }

    /**
     * @param $imageSize
     * @return string
     */
    private function getStoragePath($imageSize)
    {
        $folderName = config('filemanager.name.folder' , 'images');
        switch ($imageSize)
        {
            case 'lg':
                return 'public/'. $folderName .'/lg/';
            case 'md':
                return 'public/'. $folderName .'/md/';
            case 'sm':
                return 'public/'. $folderName .'/sm/';
            default:
                return 'public/'. $folderName .'/xl/';
        }
    }
}
