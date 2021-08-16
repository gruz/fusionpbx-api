<?php
namespace Gruz\FPBX\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Gruz\FPBX\Models\AbstractModel;
use Gruz\FPBX\Models\ContactEmail as ContactEmail;
/**
 * @OA\Schema()
 */
class Contact extends AbstractModel
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'domain_uuid', 'contact_type', 'contact_nickname'
    // ];

    protected $guarded = [
        'contact_uuid',
        'domain_uuid',
        'contact_parent_uuid',
        'last_mod_date',
        'last_mod_user', // Contains username
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
