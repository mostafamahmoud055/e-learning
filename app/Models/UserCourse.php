<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCourse extends Pivot
{
    use HasFactory;

    protected $table = 'users_courses';
    protected $hidden = ['created_at', 'updated_at','course_id', 'user_id'];

}
