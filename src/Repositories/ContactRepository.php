<?php

namespace Gruz\FPBX\Repositories;

use Gruz\FPBX\Models\Contact;
use Gruz\FPBX\Models\AbstractModel;
use Gruz\FPBX\Repositories\AbstractRepository;

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
