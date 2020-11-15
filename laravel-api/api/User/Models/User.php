<?php

namespace Api\User\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\FusionPBXTableModel;
use Api\Extension\Models\Extension;
use Api\Status\Models\Status;
use Illuminate\Contracts\Auth\MustVerifyEmail;
// ~ use LaravelCustomRelation\HasCustomRelations;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, FusionPBXTableModel;
    // ~ use HasCustomRelations;

    // protected $keyType = 'uuid';

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
        // We here hide native user_status field, as we use another more wide table for user status
        // and not sure how the field is intended to be used in the native FusionPBX
        'user_status',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'v_group_users', 'user_uuid', 'group_uuid');
    }

    public function status()
    {
        return $this->hasOne(Status::class, 'user_uuid', 'user_uuid');
    }

    public function domain()
    {
        return $this->hasOne(Domain::class, 'domain_uuid', 'domain_uuid');
    }

    /**
     * Get the related permissions
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function permissions()
    {
        // Do not delete the comments below

        // This code works as expected only because of an override Group_user->getKeyName method.
        // Otherwise Laravel builds a wrong query
        return $this->hasManyThrough(Group_permission::class, Group_user::class, 'user_uuid', 'group_name', 'user_uuid');

        // Other Gruz tries

        // A good link to examine https://gist.github.com/alexweissman/cae6303d2476d18b4a10eeef38919fcf

        // Returning a direct query result fits only partially, as a Relation has more methods.
        // So because we want to have a Relation, we refused to use the code below
        /*
        return $permissions = \DB::table('v_group_permissions')
                    ->select('v_group_permissions.permission_name')
                    ->join('v_group_users', 'v_group_users.group_name', '=', 'v_group_permissions.group_name')
                    ->where('v_group_users.user_uuid', '=', $this->user_uuid);
                    //->get();
        */

        /*
         * It was a try to use a Custom relation package relations
         * https://github.com/johnnyfreeman/laravel-custom-relation
         * At least `2017-06-10 03:17:49` it demanded a fork
         * https://github.com/36864/laravel-custom-relation
         *
         * So composer.json should include for the code below to work
          "require": {
              "johnnyfreeman/laravel-custom-relation": "dev-master"
          },
          "repositories": [
              {
                  "type": "vcs",
                  "url":  "git@github.com:36864/laravel-custom-relation.git"
              }
          ],
         *
         * But the code didn't work, as returned null when trying to use ->with(['permissions'])
        return $this->custom(
            Group_permission::class,

            // add constraints
            function ($relation) {
                $relation->getQuery()
                    //->select('v_group_permissions.permission_name')
                    ->join('v_group_users', 'v_group_users.group_name', '=', 'v_group_permissions.group_name')
                    ->where('v_group_users.user_uuid', '=', $this->user_uuid);
            },

            // add eager constraints
            function ($relation, $models) {
                //$relation->getQuery()->whereIn('role_user.user_id', $relation->getKeys($models));
            }
        );
         * */
    }

    public function getDomainAdmins()
    {
      // ~ $admins = User::where([
      return  User::where([
                  'domain_uuid' => $this->domain_uuid,
                  'user_enabled' => 'true'
                //])->with('permissions')->where('permission_name', 'in', ['user_add', 'user_edit']);
                ])->whereHas('permissions', function($query) {

                  $query->whereIn('permission_name', ['user_add', 'user_edit']);
                })->with(['emails']);

      // ~ return $admins;
    }

    public function emails()
    {
        return $this->hasMany(Contact_email::class, 'contact_uuid', 'contact_uuid');
    }

    public function pushtokens()
    {
        return $this->hasMany(Pushtoken::class, 'user_uuid', 'user_uuid');
    }

    public function extensions()
    {
        return $this->belongsToMany(Extension::class, 'v_extension_users', 'user_uuid', 'extension_uuid');
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
