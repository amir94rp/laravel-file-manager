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
        $settings   = json_decode($request->input('settings') , true);

        foreach ($images as $index=>$image)
        {
            $this->multiSize($image , $uploadPath , $settings[$index]);
        }
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
        $directories_array = [];

        foreach ($directories as $index=>$directory){
            $directory = Str::replace($this->getStoragePath('xl') , '' , $directory);

            $info = ['id' => $index , 'directory' => $directory ,
                'path' => pathinfo($directory) , 'parent_id' => null , 'inner' => []];

            $key = array_search( $info['path']['dirname'] , array_column($directories_array , 'directory'));

            if (is_numeric($key)){
                $directories_array[$key]['inner'][] = $info['id'];
                $info['parent_id'] = $directories_array[$key]['id'];
            }

            $directories_array[] = $info;
        }

        $files = Storage::files($directoryPath);
        $files_array = [];

        foreach ($files as $single_file){

            $file_info['name'] = pathinfo($single_file)['basename'];

            $file_info['url'] = [
                'xl' => Storage::url($single_file),
                'lg' => Storage::url(
                    Str::replace($this->getStoragePath('xl') , $this->getStoragePath('lg') , $single_file)
                ),
                'md' => Storage::url(
                    Str::replace($this->getStoragePath('xl') , $this->getStoragePath('md') , $single_file)
                ),
                'sm' => Storage::url(
                    Str::replace($this->getStoragePath('xl') , $this->getStoragePath('sm') , $single_file)
                ),
            ];

            $file_info['time'] = Storage::lastModified($single_file);
            $file_info['path'] = $single_file;

            $files_array[] = $file_info;
        }

        return ['directories' => $directories_array , 'files' => $files_array ];
    }

    /**
     * @param Request $request
     * @return array[]
     */
    public function selectImages(Request $request){
        $request->validate([
            'images'    => ['required' , 'array']
        ]);

        $images = [];

        foreach ($request->get('images') as $single_file){
            $lg_image_file = Str::replace($this->getStoragePath('xl') , $this->getStoragePath('lg') , $single_file);
            $md_image_file = Str::replace($this->getStoragePath('xl') , $this->getStoragePath('md') , $single_file);
            $sm_image_file = Str::replace($this->getStoragePath('xl') , $this->getStoragePath('sm') , $single_file);

            $xl_url_string = Storage::url($single_file);

            $xl_image_size = getimagesize(Storage::path($single_file));
            $lg_image_size = getimagesize(Storage::path($lg_image_file));
            $md_image_size = getimagesize(Storage::path($md_image_file));
            $sm_image_size = getimagesize(Storage::path($sm_image_file));

            $images[] = [
                'xl' => [
                    'url'   => $xl_url_string,
                    'size'  => $this->fileSizeFormat($single_file),
                    'height' => $xl_image_size[1],
                    'width' => $xl_image_size[0],
                ],

                'lg' => [
                    'url'   => Storage::url($lg_image_file),
                    'size'  => $this->fileSizeFormat($lg_image_file),
                    'height' => $lg_image_size[1],
                    'width' => $lg_image_size[0],
                ],

                'md' => [
                    'url'   => Storage::url($md_image_file),
                    'size'  => $this->fileSizeFormat($md_image_file),
                    'height' => $md_image_size[1],
                    'width' => $md_image_size[0],
                ],

                'sm' => [
                    'url'   => Storage::url($sm_image_file),
                    'size'  => $this->fileSizeFormat($sm_image_file),
                    'height' => $sm_image_size[1],
                    'width' => $sm_image_size[0],
                ],

                'select' => [
                    'url' => $xl_url_string,
                    'alt' => ''
                ]
            ];
        }

        return ['images' => $images];
    }

    /**
     * @param $imageFile
     * @param $directory
     * @param $settings
     */
    private function multiSize($imageFile , $directory , $settings){

        $format = $settings['format'];
        $name = $this->slugString($settings['name']);
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
        $savePath = $xlPath . $fileName; $counter = 1;

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

    /**
     * @param $string
     * @return array|string|string[]|null
     */
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

    /**
     * @param $path
     * @return string
     */
    private function fileSizeFormat($path)
    {
        $size = Storage::size($path) ; $units = array( 'B', 'KB', 'MB');

        $power = $size > 0 ? floor( log( $size, 1024 ) ) : 0;

        return
            number_format( $size / pow(1024, $power) , 2) . ' ' . $units[$power];
    }
}
