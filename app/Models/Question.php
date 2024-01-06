<?php

namespace App\Models;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    public $fillable = ['question', 'degree', 'assessment_id', 'course_id'];

    public $hidden = ['created_at', 'updated_at',"assessment_id","course_id"];

    public function resources()
    {
        return $this->morphMany(Resource::class, 'resourceable');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    protected function question(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }
}
