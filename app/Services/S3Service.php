<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class S3Service
{
    public $rootPrefix;
    public $disk;

    public function __construct($bucketType = 'public')
    {
        $this->disk = Storage::disk($bucketType);
        $this->rootPrefix = config('s3.root_prefix').'/';
    }

    public function putFile($fileName, $path, $fileData)
    {
        $filePath = $this->rootPrefix . $path . '/' . $fileName;

        $disk = $this->disk;
        $existsFile = $disk->exists($filePath);
        if(!$existsFile) {
            return $disk->put($filePath, $fileData);
        }
    }

    public function deleteFile($fileName, $path)
    {
        $filePath = $this->rootPrefix . $path . '/' . $fileName;
        $disk = $this->disk;
        return $disk->delete($filePath);
    }

    public function url($fileName, $folder = null)
    {
        $disk = $this->disk;
        $path = $this->rootPrefix . $folder . '/' . $fileName;
        $file = $disk->url($path);

        return $file;
    }

    public function download($fileName, $folder = null)
    {
        $disk = $this->disk;
        $path = $this->rootPrefix . $folder . '/' . $fileName;
        return $disk->download($path);
    }

    public function get($fileName, $folder = null)
    {
        $disk = $this->disk;
        $path = $this->rootPrefix . $folder . '/' . $fileName;
        $file = $disk->get($path);

        return $file;
    }
    public function put($fileName, $path, $fileData)
    {
        $filePath = $this->rootPrefix . $path . '/' . $fileName;
        $disk = $this->disk;
        return $disk->put($filePath, $fileData);
    }

    public function checkFileExists($fileName, $path)
    {
        $filePath = $this->rootPrefix . $path . '/' . $fileName;

        $disk = $this->disk;
        return $disk->exists($filePath);
    }
}
