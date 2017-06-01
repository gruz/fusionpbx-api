<?php
namespace Api\Users\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Contact extends Model
{
    use Notifiable, FusionPBXTableModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_uuid', 'contact_type', 'contact_nickname'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'password', 'remember_token',
    ];

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
