<?php

namespace Api\PostponedAction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostponedAction extends Model
{
    use HasFactory;

    protected $casts = [
        'request' => 'json',
    ];
}
