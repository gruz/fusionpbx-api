<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UuidsTrait
{
    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }
}
