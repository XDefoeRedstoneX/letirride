<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'image', 'sort_order', 'is_active'];

    public function getImageUrlAttribute(): string
    {
        return asset($this->image);
    }
}
