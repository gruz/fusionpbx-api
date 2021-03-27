<?php

namespace Api\User\Repositories;

use Api\User\Models\Contact;
use Infrastructure\Database\Eloquent\AbstractModel;
use Infrastructure\Database\Eloquent\AbstractRepository;

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

    // public function setGroups(User $user, array $addGroups, array $removeGroups = [])
    // {
    //     $this->database->beginTransaction();

    //     try {
    //         if (count($removeGroups) > 0) {
    //             $query = $this->database->table($user->groups()->getTable());
    //             $query
    //                 ->where('user_id', $user->id)
    //                 ->whereIn('group_id', $removeGroups)
    //                 ->delete();
    //         }

    //         if (count($addGroups) > 0) {
    //             $query = $this->database->table($user->groups()->getTable());
    //             $query
    //                 ->insert(array_map(function ($groupId) use ($user) {
    //                     return [
    //                         'group_id' => $groupId,
    //                         'user_id' => $user->id
    //                     ];
    //                 }, $addGroups));
    //         }
    //     } catch (Exception $e) {
    //         $this->database->rollBack();

    //         throw $e;
    //     }

    //     $this->database->commit();
    // }
}
