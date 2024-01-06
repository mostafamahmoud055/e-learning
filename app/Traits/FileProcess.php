<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Filesystem\Filesystem;

trait FileProcess
{

    public function uploadFile($request, $folder)
    {
        if (!$request->hasFile('photo') && !$request->hasFile('file'))
            return null;
        $photo = $request->file('photo'); // UploadedFile Object
        if (!$photo) {
            $time = Carbon::now()->timestamp;
            $path = [];
            $files = $request->file('file'); // UploadedFile Object
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $fileName =  pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $fileName = $time . '_' . $fileName . '.' . $extension;
                $path[] = $file->storeAs($folder, $fileName);
                $time++;
            }
        } else {
            $path = $photo->store($folder);
        }

        return $path;
    }
    public function deleteDir($dir)
    {
        $FileSystem = new Filesystem();
        $directory = public_path() . '/images/' . $dir;
        if ($FileSystem->exists($directory)) {
            $files = $FileSystem->files($directory);
            if (empty($files)) {
                $FileSystem->deleteDirectory($directory);
            }
        }
    }
}
