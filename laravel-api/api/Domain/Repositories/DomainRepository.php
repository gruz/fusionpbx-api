<?php

namespace Api\Domain\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Api\User\Models\Contact;
use Api\Domain\Models\Domain;
use Api\User\Models\ContactUser;
use Infrastructure\Database\Eloquent\AbstractRepository;

class DomainRepository extends AbstractRepository
{
    private $userRepository;

    public function createTODEL(array $data)
    {
        /**
         * @var Domain
         */
        $domain = $this->getModel();

        // ~ $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $domain->fill($data);
        $domain->save();

        return $domain;

        $usersData = Arr::get($data, 'users');
        $domain->domain_settings()->createMany(Arr::get($data, 'settings'));

        // $this->userRepository->createMany
 
        $domain->users()->createMany($usersData);
        foreach ($domain->users as $k => $user) {

            $contactsData = Arr::get($usersData, $k . '.contacts');
            $this->userRepository->attach($user, 'contacts', $contactsData);
            $contactsData = collect($contactsData)->map(function($item, $key) use ($domain) {
                $item['contact_uuid'] = Str::uuid();
                $item['domain_uuid'] = $domain->domain_uuid;
                return $item;
            });

            Contact::insert($contactsData->toArray());

            foreach ($contactsData as $contactData) {
                $contact_userData[] = [
                    'contact_user_uuid' => Str::uuid(),
                    'domain_uuid' => $domain->domain_uuid,
                    'contact_uuid' => $contactData['contact_uuid']->toString(),
                    'user_uuid' => $user->user_uuid,
                ];
            }
        }
        ContactUser::insert($contact_userData);
        // dd($contact_userData);
        // dd($domain->users->contacts()->createMany($contacts));
        // $domain->refresh();
        // dd($domain->domain_settings->toArray());
        // dd($domain->users->toArray(), $contacts->toArray());
dd($domain);
        return $domain;
    }

    public function setGroups(User $user, array $addGroups, array $removeGroups = [])
    {
        $this->database->beginTransaction();

        try {
            if (count($removeGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->where('user_id', $user->id)
                    ->whereIn('group_id', $removeGroups)
                    ->delete();
            }

            if (count($addGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->insert(array_map(function ($groupId) use ($user) {
                        return [
                            'group_id' => $groupId,
                            'user_id' => $user->id
                        ];
                    }, $addGroups));
            }
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
