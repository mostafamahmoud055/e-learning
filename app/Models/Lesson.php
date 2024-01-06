<?php

namespace App\Models;

use App\Models\Resource;
use App\Observers\LessonObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;
    public $fillable = ['name', 'description', 'course_id', 'module_id', 'hours'];
    public $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function resources()
    {
        return $this->morphMany(Resource::class, 'resourceable');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function LessonCompleted()
    {
        return $this->hasOne(LessonCompleted::class);
    }


}
