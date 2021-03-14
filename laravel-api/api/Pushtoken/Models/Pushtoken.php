<?php

namespace Api\Pushtoken\Models;

use Infrastructure\Database\Eloquent\AbstractModel;
use Infrastructure\Traits\Uuids;

class Pushtoken extends AbstractModel
{
    use Uuids;

    var $primaryKey = 'pushtoken_uuid';

    var $incrementing = false;

    var $guarded = ['pushtoken_uuid', 'created_at', 'updated_at'];
    //var $fillable = ['token_type', 'token', 'token_class', 'user_uuid'];
}
