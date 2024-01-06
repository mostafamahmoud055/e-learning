<?php

namespace App\Models;

use App\Models\User;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Traits\FilesLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory, FilesLink, SoftDeletes;
    protected $fillable = [
        'name',
        'subject',
        'description',
        'image',
        'grade',
        'class',
        'rate',
        'active',
        'target',
        'user_id',
        'hours'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'user_id',
        'image',
        'deleted_at'
    ];
    protected $appends = [
        'number_of_student',
        'photo',
        'created_from',
        'updated_from'
    ];
    public function getCreatedFromAttribute()
    {
        return $this->created_at->format('d M Y');
    }
    public function getUpdatedFromAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function scopeAuthor($query)
    {
        return $query->with(['teacher' =>  function ($query) {
            $query->select('id', 'first_name', 'last_name', 'username', 'code', 'image');
        }]);
    }
    protected function class(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }
    protected function target(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function students()
    {
        return $this->belongsToMany(Student::class, 'users_courses', 'course_id', 'user_id', 'id', 'id')->withPivot(['progress'])->withTimestamps();
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function lessons()
    {   
        return $this->hasMany(Lesson::class);
    }

    public function getNumberOfStudentAttribute()
    {
        return $this->belongsToMany(Student::class, 'users_courses', 'course_id', 'user_id', 'id', 'id')->count();
    }
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
