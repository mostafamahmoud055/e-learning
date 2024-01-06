<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model
{
    use HasFactory;

    public $fillable = ['path', 'type'];
    public $hidden = [
        'created_at', 'updated_at', 'resourceable_type',
        'resourceable_id'
    ];
    public function resourceable()
    {
        return $this->morphTo();
    }

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>'http://' . $_SERVER['HTTP_HOST'] . '/' . $value
        );
    }
}
