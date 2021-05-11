<?php

namespace Api\User\Models;

use Api\User\Models\Contact;
use Api\Domain\Models\Domain;
use Api\Status\Models\Status;
use Api\User\Models\ContactUser;
use Api\User\Models\UserSetting;
use Laravel\Passport\HasApiTokens;
use Api\Extension\Models\Extension;
use Api\User\Models\GroupPermission;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Api\Extension\Models\ExtensionUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Infrastructure\Database\Eloquent\AbstractModel;
use Infrastructure\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * @OA\Schema()
 */
class User extends AbstractModel implements
    MustVerifyEmailContract,
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use HasApiTokens, Notifiable;
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
    use HasFactory;
    // ~ use HasCustomRelations;

    public $timestamps = true;

    const CREATED_AT = 'add_date';
    const UPDATED_AT = null;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        // 'contact_uuid',
        // 'user_enabled',
        // 'add_user',
        // 'add_date',
        'user_email',
        'salt',
        'email',
        // We here hide native user_status field, as we use another more wide table for user status
        // and not sure how the field is intended to be used in the native FusionPBX
        'user_status',  // user_status can be ["Available", "Available (On Demand)",
        // "On Break", "Do Not Disturb", "Logged Out"] - user can edit it
    ];

    /**
     * The attributes that should not be assinable.
     * Only explicit assigns in repository.
     *
     * @var array
     */
    protected $guarded = [
        'user_uuid',
        'domain_uuid',
        'contact_uuid',
        'salt',
        'api_key',
        // We here hide native user_status field, as we use another more wide table for user status
        // and not sure how the field is intended to be used in the native FusionPBX
        // 'user_status',
        'user_status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'email'
    ];

    /**
     * _fuda_:
     *      Gets user email. Needs to be appended
     *      cause fusionpbx has named user email attribute as "user_email"
     *      what is not obviouse for reset password broker which expects email property
     *      to reset the password.
     *
     * @return string User email address
     */
    public function getEmailAttribute()
    {
        return $this->attributes['user_email'];
    }

    /**
     * _fuda_:
     *      Sets user email address.
     *      See public function getEmailAttribute() to get why.
     */
    public function setEmailAttribute($email)
    {
        $this->attributes['user_email'] = $email;
    }

    // /**
    //  * _fuda_:
    //  *      Sets user email address for fusionpbx
    //  *      and appended email attribute for password reset.
    //  */
    // public function setUserEmailAttribute($email)
    // {
    //     $this->attributes['user_email'] = $email;
    //     $this->setEmailAttribute($email);
    // }

    public function groups()
    {
        return $this->belongsToMany(Group::class, UserGroup::class, 'user_uuid', 'group_uuid');
    }

    public function status(): HasOne
    {
        return $this->hasOne(Status::class, 'user_uuid', 'user_uuid');
    }

    public function domain(): HasOne
    {
        return $this->hasOne(Domain::class, 'domain_uuid', 'domain_uuid');
    }

    /**
     * Get the related permissions
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function permissions(): HasManyThrough
    {
        // Do not delete the comments below

        // This code works as expected only because of an override Group_user->getKeyName method.
        // Otherwise Laravel builds a wrong query
        return $this->hasManyThrough(GroupPermission::class, UserGroup::class, 'user_uuid', 'group_name', 'user_uuid');

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
            GroupPermission::class,

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
        $admins = User::where([
            'domain_uuid' => $this->domain_uuid,
            'user_enabled' => 'true'
            //])->with('permissions')->where('permission_name', 'in', ['user_add', 'user_edit']);
        ])
            // ->where('user_enabled', '!=', 'true')
            ->whereHas('permissions', function ($query) {

                $query->whereIn('permission_name', ['user_add', 'user_edit']);
            })->with(['emails']);

        return $admins->get();
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ContactEmail::class, 'contact_uuid', 'contact_uuid');
    }

    public function pushtokens(): HasMany
    {
        return $this->hasMany(Pushtoken::class, 'user_uuid', 'user_uuid');
    }

    public function extensions(): BelongsToMany
    {
        return $this->belongsToMany(Extension::class, ExtensionUser::class, 'user_uuid', 'extension_uuid');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, ContactUser::class, 'user_uuid', 'contact_uuid');
    }

    public function user_settings(): HasMany
    {
        return $this->hasMany(UserSetting::class, 'user_uuid', 'user_uuid');
    }

    public function getResellerCodeAttribute()
    {
        return optional($this->user_settings->where('user_setting_category', 'payment')->where('user_setting_subcategory', 'reseller_code')->first())->user_setting_value;
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
        if (md5($this->salt . $password) == $this->password) {
            return true;
        } else {
            return false;
        }
    }

    // /**
    //  * Method to return the email for password reset
    //  *
    //  * @return string Returns the User Email Address
    //  */
    // public function getEmailForPasswordReset() {

    //     $email = $this->getAttribute('user_email');
    //     if (!$email) {
    //         // 1 Throught Contacts
    //         $email = $this->emails()
    //                       ->get()
    //                       ->first()
    //                       ->toArray()['email_address'];

    //         // 2 Throught model property
    //         // $email = $this->user_email;
    //     }

    //     return $email;
    // }

    /**
     * Method to get user domain name to which he relates.
     */
    // public function getDomainNameAttribute()
    // {
    //     return $this->domain()->first()->getAttribute('domain_name');
    // }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->getAttribute('user_email');
    }

    public function getNameAttribute()
    {
        return $this->username;
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return 'true' === $this->user_enabled;
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'user_enabled' => 'true',
        ])->save();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token, $this));
    }
}
