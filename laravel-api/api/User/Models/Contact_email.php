<?php
namespace Api\User\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Contact_email extends Model
{
    use Notifiable, FusionPBXTableModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_uuid', 'contact_uuid', 'email_primary', 'email_address', 'email_description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'domain_uuid', 'contact_uuid',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'v_domians', 'domain_uuid', 'domain_uuid');
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
