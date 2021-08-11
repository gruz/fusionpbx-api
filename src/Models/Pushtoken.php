<?php

namespace Gruz\FPBX\Models;

use Gruz\FPBX\Models\AbstractModel;

class Pushtoken extends AbstractModel
{
    var $primaryKey = 'pushtoken_uuid';

    var $incrementing = false;

    var $guarded = ['pushtoken_uuid', 'created_at', 'updated_at'];
    //var $fillable = ['token_type', 'token', 'token_class', 'user_uuid'];
}
