<?php

namespace App\Traits;

use App\Models\Course;
use App\Models\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait Trashing
{

    public function trashPush($modelName, $course_id = null)
    {
        $className = explode('\\', $modelName)[2];
        if ($className == "Course") {
            $collection =  $modelName::where("user_id", Auth::id())->onlyTrashed()->get();
            return $this->returnData("$className in trash", $collection);
        }
        if ($className == "Lesson") {
            $collection =  Course::with('lessons')->where("user_id", Auth::id())->get();
            $collection =  $modelName::whereIn("course_id", $collection->pluck('id'))->onlyTrashed()->get();
            return $this->returnData("$className in trash", $collection->load('course'));
        }
    }

    public function trashRestore(string $id, $modelName)
    {
        $className = (explode('\\', $modelName))[2];
        $collection =  $modelName::onlyTrashed()->find($id);
        if (!$collection)
            return $this->returnError("$className not found", 404);
        $this->authorize('restore', $collection);
        $collection->restore();
        return $this->returnSuccessMessage("$className restored successfully");
    }

    public function trashForceDelete($collection, $className)
    {
        if (!$collection)
            return $this->returnError("$className not found", 404);
        $this->authorize('forceDelete', $collection);
        if ($collection->resources) {
            foreach ($collection->resources as $resource) {
                $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images/',  '', $resource->path);
                Storage::delete($image);
            }
            $collection->resources()->where('resourceable_id', $collection->id)->delete();
        } else {
            $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images/',  '', $collection->image);
            Storage::delete($image);
        }
        $dir = dirname($image);
        $collection->forceDelete();
        $this->deleteDir($dir);
        return $this->returnSuccessMessage("course deleted forever");
    }
}
