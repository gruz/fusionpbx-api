<?php

namespace Api\Status\Models;

use App\Database\Eloquent\Model;
use App\Traits\Uuids;

class Status extends Model
{
    use Uuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'statuses';

    var $primaryKey = 'status_uuid';

    var $incrementing = false;

    var $guarded = ['status_uuid', 'created_at', 'updated_at'];
    //var $fillable = ['token_type', 'token', 'token_class', 'user_uuid'];
}
