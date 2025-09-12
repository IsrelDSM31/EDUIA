<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    protected $fillable = [
        'name',
        'description',
        'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];
}
