<?php

namespace Infrastructure\Traits;

use Illuminate\Support\Str;

trait Uuids
{
    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid();
        });
    }
}
