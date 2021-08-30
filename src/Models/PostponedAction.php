<?php

namespace Gruz\FPBX\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostponedAction extends Model
{
    use HasFactory;

    protected $casts = [
        'request' => 'json',
    ];

    protected $fillable = [
        'code',
        'request',
    ];

    protected $hidden = [
        'code',
        'id',
    ];

    public static function last()
    {
        return self::latest()->first();
    }
}
