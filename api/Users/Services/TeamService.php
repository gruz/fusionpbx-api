<?php

namespace Api\Users\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

use Api\Users\Services\UserService;
use \Api\Users\Exceptions\InvalidGroupException;
use \Api\Users\Exceptions\UserNotFoundException;
use \Api\Users\Exceptions\DomainExistsException;
use \Api\Users\Events\UserWasCreated;
use \Api\Users\Events\DomainWasCreated;
use \Api\Users\Events\UserWasDeleted;
use \Api\Users\Events\UserWasUpdated;

use \Api\Users\Repositories\GroupRepository;
use \Api\Users\Repositories\UserRepository;
use \Api\Users\Repositories\DomainRepository;
use \Api\Users\Repositories\ContactRepository;
use \Api\Users\Repositories\Contact_emailRepository;

class TeamService
{
    private $auth;

    private $database;

    private $dispatcher;

    private $groupRepository;

    private $userRepository;

    private $domainRepository;

    private $contactRepository;

    private $contact_emailRepository;

    private $userService;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        DomainRepository $domainRepository,
        ContactRepository $contactRepository,
        Contact_emailRepository $contact_emailRepository,
        UserService $userService
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
        $this->contactRepository = $contactRepository;
        $this->contact_emailRepository = $contact_emailRepository;
        $this->userService = $userService;
    }

    public function getAll($options = [])
    {
        return $this->userRepository->get($options);
    }

    public function getById($userId, array $options = [])
    {
        $user = $this->getRequestedUser($userId);

        return $user;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
						$data['domain_name'] =  $data['domain_name'] . '.' . env('MOTHERSHIP_DOMAIN');

						if ($this->domainRepository->getWhere('domain_name', $data['domain_name'])->count() > 0)
						{
							throw new DomainExistsException($data['domain_name']);
						}

						$data['domain_enabled'] =  'true';
						$data['domain_description'] =  'Created via api at ' . date( 'Y-m-d H:i:s', time() );

            $domain = $this->domainRepository->create($data);

            // ~ $this->dispatcher->fire(new DomainWasCreated($domain));

            $data['domain_uuid'] = $domain->getAttribute('domain_uuid');
            $data['contact_type'] = 'user';
            $data['contact_nickname'] = $data['email'];


            $contact = $this->contactRepository->create($data);
            $contact->addHidden(['domain_uuid']);
            $data['contact_uuid'] = $contact->getAttribute('contact_uuid');

            $data['email_primary'] = 1;
            $data['email_address'] = $data['email'];

            $contact_email = $this->contact_emailRepository->create($data);
            $contact_email->addHidden(['domain_uuid', 'contact_uuid']);

            // ~ $data['username'] = $data['email'];
            $data['user_enabled'] = 'true';
            // $data['add_user'] = 'admin';
            // $data['add_date'] = 'admin';

            $user = $this->userRepository->create($data);
            $user->addHidden(['domain_uuid', 'contact_uuid']);

            // Get default group for a new team
            $group = $this->groupRepository->getWhere('group_name', env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME'));
            $data['group_uuid'] = $group->first()->group_uuid;

            $this->userService->setGroups($user->user_uuid, [$data['group_uuid']]);
            //$user = $this->userService->addGroup($data);


            $contact->setRelation('contact_email', $contact_email);
            $user->setRelation('contact', $contact);
            $domain->setRelation('admin_user', $user);



            $domain->message = __('messages.team created', [
                'username' => $data['username'],
                'domain_name' => $data['domain_name'],
                'password' => $data['password']
              ]);

            // ~ $this->dispatcher->fire(new TeamWasCreated($domain));

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $domain;
    }

    public function update($userId, array $data)
    {
        $user = $this->getRequestedUser($userId);

        $this->database->beginTransaction();

        try {
            $this->userRepository->update($user, $data);

            $this->dispatcher->fire(new UserWasUpdated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function delete($userId)
    {
        $user = $this->getRequestedUser($userId);

        $this->database->beginTransaction();

        try {
            $this->userRepository->delete($userId);

            $this->dispatcher->fire(new UserWasDeleted($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    public function addGroups($userId, array $groupIds)
    {
        $user = $this->getRequestedUser($userId, [
            'includes' => ['groups']
        ]);

        $currentGroups = $user->groups->pluck('id')->toArray();
        $groups = $this->checkValidityOfGroups($groupIds);

        $this->userRepository->setGroups($user, $groupIds);

        $groups
            ->filter(function ($group) use ($currentGroups) {
                return !in_array($group->id, $currentGroups);
            })
            ->each(function ($group) use ($user) {
                $user->groups->add($group);
            });

        return $user;
    }

    public function setGroups($userId, array $groupIds)
    {
        $user = $this->getRequestedUser($userId, [
            'includes' => ['groups']
        ]);

        $currentGroups = $user->groups->pluck('id')->toArray();
        $groups = $this->checkValidityOfGroups($groupIds);

        $remove = array_diff($currentGroups, $groupIds);
        $add = array_diff($groupIds, $currentGroups);

        $this->userRepository->setGroups($user, $add, $remove);

        $user->setRelation('groups', new Collection($groups));

        return $user;
    }

    public function removeGroups($userId, array $groupIds)
    {
        $user = $this->getRequestedUser($userId, [
            'includes' => ['groups']
        ]);

        $groups = $this->checkValidityOfGroups($groupIds);

        $this->userRepository->setGroups($user, [], $groupIds);

        $updatedGroupCollection = $user->groups->filter(function ($group) use ($groupIds) {
            return !in_array($group->id, $groupIds);
        });
        $user->setRelation('groups', $updatedGroupCollection);

        return $user;
    }

    private function checkValidityOfGroups(array $groupIds = [])
    {
        $groups = $this->groupRepository->getWhereIn('id', $groupIds);

        if (count($groupIds) !== $groups->count()) {
            $missing = array_diff($groupIds, $groups->pluck('id')->toArray());
            throw new InvalidGroupException($missing[0]);
        }

        return $groups;
    }

    private function getRequestedUser($userId, array $options = [])
    {
        $user = $this->userRepository->getById($userId, $options);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
