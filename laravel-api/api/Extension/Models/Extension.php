<?php

namespace Api\Extension\Models;

use Api\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema()
 */

 class Extension extends AbstractModel
{
    use HasFactory;
    use Notifiable;

    protected $attributes = [
      'directory_visible' => 'true',
      'directory_exten_visible' => 'true',
      'limit_max' => 5,
      'limit_destination' => 'error/user_busy',
      'call_timeout' => 30,
      'call_screen_enabled' => 'false',
      'hold_music' => 'local_stream://default',
      'nibble_account' => null,
      'sip_force_expires' => null,
      'mwi_account' => null,
      'unique_id' => null,
      'dial_string' => null,
      'dial_user' => null,
      'dial_domain' => null,
      'do_not_disturb' => null,
      'forward_all_destination' => null,
      'forward_all_enabled' => null,
      'forward_busy_destination' => null,
      'forward_busy_enabled' => null,
      'forward_no_answer_destination' => null,
      'forward_no_answer_enabled' => null,
      'forward_user_not_registered_destination' => null,
      'forward_user_not_registered_enabled' => null,
      'follow_me_uuid' => null,
      'enabled' => 'true',
      'forward_caller_id_uuid' => null,
      'absolute_codec_string' => null,
    ];

    protected $guarded = [
        'extension_uuid',
        'domain_uuid',

        'number_alias',
    ];

    // protected $fillable = [
    //     'domain_uuid',
    //     'extension',
    //     'number_alias',
    //     'password',
    //     'accountcode',
    //     'effective_caller_id_name',
    //     'effective_caller_id_number',
    //     'outbound_caller_id_name',
    //     'outbound_caller_id_number',
    //     'emergency_caller_id_name',
    //     'emergency_caller_id_number',
    //     'directory_visible',
    //     'directory_exten_visible',
    //     'limit_max',
    //     'limit_destination',
    //     'missed_call_app',
    //     'missed_call_data',
    //     'user_context',
    //     'toll_allow',
    //     'call_timeout',
    //     'call_group',
    //     'call_screen_enabled',
    //     'user_record',
    //     'hold_music',
    //     'auth_acl',
    //     'cidr',
    //     'sip_force_contact',
    //     'nibble_account',
    //     'sip_force_expires',
    //     'mwi_account',
    //     'sip_bypass_media',
    //     'unique_id',
    //     'dial_string',
    //     'dial_user',
    //     'dial_domain',
    //     'do_not_disturb',
    //     'forward_all_destination',
    //     'forward_all_enabled',
    //     'forward_busy_destination',
    //     'forward_busy_enabled',
    //     'forward_no_answer_destination',
    //     'forward_no_answer_enabled',
    //     'forward_user_not_registered_destination',
    //     'forward_user_not_registered_enabled',
    //     'follow_me_uuid',
    //     'enabled',
    //     'description',
    //     'forward_caller_id_uuid',
    //     'absolute_codec_string',
    //     'force_ping'
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        // ~ 'user_context',
        'number_alias',
    ];

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'v_extension_users', 'extension_uuid', 'user_uuid');
    }
}
