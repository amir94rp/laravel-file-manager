<?php

namespace FileManager\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as InterventionImage;

class FileManagerController extends Controller
{
    private $storagePath;
    private $lgPath;
    private $mdPath;
    private $smPath;

    private $imageLgSize;
    private $imageMdSize;
    private $imageSmSize;

    public function __construct()
    {
        $name = config('filemanager.name' , 'laravel-file-manager');
        $this->storagePath  = 'public/'. $name .'/xl/';
        $this->lgPath       = 'public/'. $name .'/lg/';
        $this->mdPath       = 'public/'. $name .'/md/';
        $this->smPath       = 'public/'. $name .'/sm/';

        $this->imageLgSize = config('filemanager.image.lg' , 500);
        $this->imageMdSize = config('filemanager.image.md' , 250);
        $this->imageSmSize = config('filemanager.image.sm' , 100);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function createFolder(Request $request){

        $request->validate([
            'name'  => ['required' , 'string'],
            'path'  => ['nullable' , 'string']
        ]);

        $name = $request->input('name');
        $path = $request->input('path');

        Storage::makeDirectory($this->storagePath . $path . '/' . $name);
        Storage::makeDirectory($this->lgPath . $path . '/' . $name);
        Storage::makeDirectory($this->mdPath . $path . '/' . $name);
        Storage::makeDirectory($this->smPath . $path . '/' . $name);

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

        Storage::deleteDirectory($this->storagePath . $folderPath);
        Storage::deleteDirectory($this->lgPath . $folderPath);
        Storage::deleteDirectory($this->mdPath . $folderPath);
        Storage::deleteDirectory($this->smPath . $folderPath);

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
            Storage::delete(Str::replace($this->storagePath , $this->lgPath , $image));
            Storage::delete(Str::replace($this->storagePath , $this->mdPath , $image));
            Storage::delete(Str::replace($this->storagePath , $this->smPath , $image));
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws \Intervention\Image\Exception\NotReadableException
     */
    public function uploadImages(Request $request){

        $request->validate([
            'images'        => ['required'] ,
            'images.*'      => ['image'] ,
            'path'          => ['nullable' , 'string']
        ]);

        $images = $request->file('images');
        $uploadPath = $request->input('path');

        foreach ($images as $image){$this->multiSize($image , $uploadPath);}
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

        $directoryPath = $this->storagePath . $request->input('path');

        $directories = Storage::allDirectories($this->storagePath);
        $mutatedDirectories = [];
        foreach ($directories as $index=>$directory){

            $isExpanded = false;
            if (Str::startsWith($directoryPath , $directory)){
                $isExpanded = true;
            }

            $directory = Str::replace($this->storagePath , '' , $directory);
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
                    array_push($mutatedDirectories[$counter]['items'] , $info['id']);
                    $mutatedDirectories[$counter]['isExpandable'] = true;
                    $info['parent_id'] = $mutatedDirectory['id'];
                    break 1;
                }
            }

            array_push($mutatedDirectories , $info);
        }

        $files = Storage::files($directoryPath);
        $mutatedFiles = [];
        foreach ($files as $path){
            $file['path']   = $path;
            $file['name']   = pathinfo($path)['filename'];
            $file['url']    = Storage::url($path);
            $file['time']   = Storage::lastModified($path);;
            array_push($mutatedFiles , $file);
        }

        return [
            'directories'   => $mutatedDirectories ,
            'files'         => $mutatedFiles
        ];
    }

    /**
     * @param $imageFile
     * @param $directory
     * @throws \Intervention\Image\Exception\NotReadableException
     */
    private function multiSize($imageFile , $directory){
        $format = 'webp';
        $xlPath = $this->storagePath . $directory . '/';
        $lgPath = $this->lgPath . $directory . '/';
        $mdPath = $this->mdPath . $directory . '/';
        $smPath = $this->smPath . $directory . '/';

        $fileName = $imageFile->hashName();
        $extension = $imageFile->getClientOriginalExtension();
        $fileName = Str::replace( '.' . $extension , '' , $fileName);

        $originalImage = InterventionImage::make($imageFile)->encode($format , 100);
        $savePath = $xlPath . $fileName . "." . $format;
        Storage::put( $savePath  , $originalImage);

        $this->encodeSave($imageFile , $this->imageLgSize , $format , $lgPath , $fileName);
        $this->encodeSave($imageFile , $this->imageMdSize , $format , $mdPath , $fileName);
        $this->encodeSave($imageFile , $this->imageSmSize , $format , $smPath , $fileName);
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
        Storage::put($savePath . $imageName . "." . $imageFormat , $imageOutput);
    }
}
