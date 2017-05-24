<?php

namespace Api\Users\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

use Api\Extensions\Services\ExtensionService;


use Api\Users\Exceptions\InvalidGroupException;
use Api\Users\Exceptions\UserNotFoundException;
use Api\Users\Exceptions\DomainNotFoundException;
use Api\Users\Exceptions\UserExistsException;
use Api\Users\Exceptions\EmailExistsException;

use Api\Users\Events\UserWasCreated;
use Api\Users\Events\UserWasDeleted;
use Api\Users\Events\UserWasUpdated;

use Api\Users\Repositories\GroupRepository;
use Api\Users\Repositories\UserRepository;
use Api\Users\Repositories\DomainRepository;
use Api\Users\Repositories\ContactRepository;
use Api\Users\Repositories\Contact_emailRepository;
use Api\Extensions\Repositories\ExtensionRepository;

use Infrastructure\Traits\OneToManyRelationCRUD;


class UserService
{
    use OneToManyRelationCRUD;

    private $auth;

    private $database;

    private $dispatcher;

    private $groupRepository;

    private $userRepository;

    private $contactRepository;

    private $contact_emailRepository;

    private $extensionRepository;

    private $domainRepository;

    private $extensionService;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        ContactRepository $contactRepository,
        Contact_emailRepository $contact_emailRepository,
        ExtensionRepository $extensionRepository,
        DomainRepository $domainRepository,
        ExtensionService $extensionService
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->contact_emailRepository = $contact_emailRepository;
        $this->extensionRepository = $extensionRepository;
        $this->domainRepository = $domainRepository;
        $this->extensionService = $extensionService;
    }

    public function getMe($options = [])
    {
        return $this->auth->user();
    }

    public function getAll($options = [])
    {
				return $this->userRepository->getWhere('domain_uuid', $this->auth->user()->domain_uuid);
    }

    public function getById($userId, array $options = [])
    {
        $user = $this->getRequestedUser($userId);

        return $user;
    }

    /**
     * Creates a user
     *
     * Creates a user including all tied tables
     *
     * @param   array  $data     Data to create a user
     *
     * @return   type  Description
     */
    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            // If it's a team registration, we just create the first user in the domain.
            if ($data['isTeam'])
            {

            }
            // Otherwise we check if the username of email exists in the domain
            else
            {
              // Check if domain exists
              $domain = $this->domainRepository->getWhere('domain_name', $data['domain_name']);

              // We cannot create a user if there is not such a domain
              if ($domain->count() < 1)
              {
                throw new DomainNotFoundException();
              }

              // Get user by domain and username - create only if there is no a user with such a name
              $user = $this->userRepository->getWhereArray([
                'domain_uuid' => $domain['domain_uid'],
                'username' => $data['username'],
              ]);

              if ($user->count() > 0)
              {
                throw new UserExistsException();
              }

              // Check for the email in the current domain
              $contact_email = $this->contact_emailRepository->getWhereArray([
                'domain_uuid' => $domain['domain_uid'],
                'email_address' => $data['email'],
              ]);

              if ($contact_email->count() > 0)
              {
                throw new EmailExistsException();
              }

              $domain = $domain->first();

              $data['domain_uuid'] = $domain->getAttribute('domain_uuid');
            }


            // Create a contact
            $data['contact_type'] = 'user';
            $data['contact_nickname'] = $data['email'];

            $contact = $this->contactRepository->create($data);

            // Hide the field in the output
            $contact->addHidden(['domain_uuid']);

            // Create a email for the contact
            $data['contact_uuid'] = $contact->getAttribute('contact_uuid');

            $data['email_primary'] = 1;
            $data['email_address'] = $data['email'];

            $contact_email = $this->contact_emailRepository->create($data);

            // Hide the field in the output
            $contact_email->addHidden(['domain_uuid', 'contact_uuid']);

            // Finally create the user and hide an unneded field in the output
            $user = $this->userRepository->create($data);
            $user->addHidden(['domain_uuid', 'contact_uuid']);

            // Get group name
            $group = $this->groupRepository->getWhere('group_name', $data['group_name']);
            $data['group_uuid'] = $group->first()->group_uuid;

            // Assign the newly created user to the group
            $this->setGroups($user->user_uuid, [$data['group_uuid']]);

            // Set relations to later output it
            $contact->setRelation('contact_email', $contact_email);
            $user->setRelation('contact', $contact);

            // Create an extension
            $extension_number = $this->extensionRepository->getWhere('domain_uuid', $data['domain_uuid'])->max('extension');
            $extension_number = (int) $extension_number + 1;
            $password = bcrypt(uniqid());
            $extension = $this->extensionService->create(['extension' => $extension_number, 'password' => $password], $user);
            $this->extensionService->setUsers($extension->extension_uuid, [$user->user_uuid]);
            $user->setRelation('extension', $extension);

            $this->dispatcher->fire(new UserWasCreated($user));

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
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


}
