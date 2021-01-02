<?php

namespace Api\Pushtoken\Models;

use App\Database\Eloquent\Model;
use App\Traits\Uuids;

class Pushtoken extends Model
{
    use Uuids;

    var $primaryKey = 'pushtoken_uuid';

    var $incrementing = false;

    var $guarded = ['pushtoken_uuid', 'created_at', 'updated_at'];
    //var $fillable = ['token_type', 'token', 'token_class', 'user_uuid'];
}
