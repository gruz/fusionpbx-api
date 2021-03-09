<?php
namespace Api\User\Models;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

class Contact_user extends Model
{
    use Notifiable, FusionPBXTableModel;

    public $guarded = [];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'user_uuid');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_uuid', 'contact_uuid');
    }
}
