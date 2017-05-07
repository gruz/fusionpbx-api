<?php

namespace Api\Users\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'v_users';

    /**
     * Primary key field name
     *
     * @var string
     */
    protected $primaryKey = 'user_uuid';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'v_group_users', 'user_uuid', 'group_uuid');
    }

    /**
     * Special relationship to enable a resource filter
     * 'isAgent' for the user resource.
     * The isAgent filter will automatically eager load roles,
     * and thus need to know the roles relationship.
     */
    public function isAgent()
    {
        return $this->roles();
    }
}
