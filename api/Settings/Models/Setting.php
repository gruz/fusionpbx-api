<?php

namespace Api\Settings\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Setting extends Model
{
    use Notifiable, FusionPBXTableModel;

    protected $fillable = [
        'numbering_plan',
        'event_socket_ip_address',
        'event_socket_port',
        'event_socket_password',
        'xml_rpc_http_port',
        'xml_rpc_auth_realm',
        'xml_rpc_auth_user',
        'xml_rpc_auth_pass',
        'admin_pin',
        'mod_shout_decoder',
        'mod_shout_volume',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // ~ 'password',
    ];

}
