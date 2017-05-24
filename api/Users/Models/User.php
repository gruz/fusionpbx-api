<?php

namespace Api\Users\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Infrastructure\Traits\FusionPBXTableModel;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, FusionPBXTableModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_uuid',  'username', 'password', 'salt', 'contact_uuid', 'user_enabled', 'add_user', 'add_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'salt',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'v_group_users', 'user_uuid', 'group_uuid');
    }

    public function domain()
    {
        return $this->hasOne(Domain::class, 'domain_uuid', 'domain_uuid');
    }

    public function extensions()
    {
        $extensions = $this->hasMany(Extension::class, 'domain_uuid', 'domain_uuid');
    }

    /**
     * Special relationship to enable a resource filter
     * 'isAgent' for the user resource.
     * The isAgent filter will automatically eager load groups,
     * and thus need to know the groups relationship.
     */
    public function isAgent()
    {
        return $this->groups();
    }

    public function findForPassport(array $data)
    {
      $user = $this->where($data)->first();
      return $user;
    }

    public function validateForPassportPasswordGrant($password)
    {
      if (md5($this->salt.$password) == $this->password)
      {
        return true;
      }
      else
      {
        return false;
      }
    }
}
