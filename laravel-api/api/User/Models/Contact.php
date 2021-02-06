<?php
namespace Api\User\Models;

use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
 */
class Contact extends Model
{
    use Notifiable, FusionPBXTableModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
        // 'domain_uuid', 'contact_type', 'contact_nickname'
    // ];
    protected $guarded = [
        'contact_uuid',
        'domain_uuid',
        'contact_parent_uuid',
        'last_mod_date',
        'last_mod_user',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [];

    public function emails()
    {
        return $this->belongsToMany(ContactEmail::class, 'v_contact_emails', 'contact_uuid', 'contact_email_uuid');
    }

    /**
     * Special relationship to enable a resource filter
     * 'isAgent' for the user resource.
     * The isAgent filter will automatically eager load roles,
     * and thus need to know the roles relationship.
     */
    public function isAgent()
    {
        return $this->emails();
    }
}
