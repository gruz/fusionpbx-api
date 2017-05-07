<?php

namespace Api\Users\Models;

use Api\Users\Models\User;
use Infrastructure\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'v_groups';

    /**
     * Primary key field name
     *
     * @var string
     */
    protected $primaryKey = 'group_uuid';

    /**
     * If the primary key field in autoincrement
     *
     * Should be FALSE if it's a non-numeric field
     *
     * @var bool
     */
		public $incrementing = false;


    // const CREATED_AT = 'add_date';
    // const UPDATED_AT = 'last_update';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    //public $timestamps = false;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    //protected $dateFormat = 'U';


    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'v_group_users', 'user_uuid', 'group_uuid');
    }
}
