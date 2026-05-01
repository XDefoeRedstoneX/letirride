<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'question',
        'answer',
    ];
}
