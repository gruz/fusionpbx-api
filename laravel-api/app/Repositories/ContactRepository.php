<?php

namespace App\Repositories;

use App\Models\Contact;
use App\Database\Eloquent\AbstractModel;
use App\Database\Eloquent\AbstractRepository;

class ContactRepository extends AbstractRepository
{
    public function update(AbstractModel $contact, array $data)
    {
        $contact->fill($data);

        $contact->last_mod_date = date('now');
        $contact->last_mod_user = $data['username']; // Current user that does update
        // $contact->contact_parent_uuid = '';

        $contact->save();

        return $contact;
    }
}
