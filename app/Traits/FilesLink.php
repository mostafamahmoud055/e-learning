<?php

namespace App\Traits;

trait FilesLink
{
    public function getPhotoAttribute()
    {
        if (str_starts_with($this->image, 'https')) {
            return $this->image;
        }
        return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->image;
    }
}
