<?php

namespace Api\User\Services;

use Exception;
use Illuminate\Support\Arr;
use Api\Extension\Models\Extension;
use Api\User\Events\UserWasCreated;
use Api\User\Events\UserWasDeleted;
use Api\User\Events\UserWasUpdated;
use Illuminate\Support\Facades\Auth;
use Api\User\Repositories\UserRepository;
use Api\User\Repositories\GroupRepository;
use Api\Extension\Services\ExtensionService;
use Api\User\Exceptions\UserExistsException;
use Api\User\Repositories\ContactRepository;
use Api\Domain\Repositories\DomainRepository;
use Infrastructure\Traits\OneToManyRelationCRUD;
use Api\User\Repositories\ContactEmailRepository;
use Api\Domain\Exceptions\DomainNotFoundException;
use Api\Extension\Repositories\ExtensionRepository;
use Api\User\Events\UserWasActivated;
use Infrastructure\Database\Eloquent\AbstractService;
use Api\User\Exceptions\ActivationHashNotFoundException;

class UserService extends AbstractService
{
    use OneToManyRelationCRUD;

    private $groupRepository;

    private $userRepository;

    private $contactRepository;

    /**
     * @var ContactEmailRepository
     */
    private $contact_emailRepository;

    private $extensionRepository;

    private $domainRepository;

    private $extensionService;

    private $scope;

    public function __construct(
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        ContactRepository $contactRepository,
        ContactEmailRepository $contact_emailRepository,
        ExtensionRepository $extensionRepository,
        DomainRepository $domainRepository,
        ExtensionService $extensionService
    ) {
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->contact_emailRepository = $contact_emailRepository;
        $this->extensionRepository = $extensionRepository;
        $this->domainRepository = $domainRepository;
        $this->extensionService = $extensionService;

        parent::__construct();
    }

    public function getMe($options = [])
    {
        //return Auth::user();
        $class = Extension::class;
        $class::$staticMakeVisible = ['password'];
        return $this->userRepository->getWhere('user_uuid', Auth::user()->user_uuid)->first();
    }


    // public function getByAttributes(array $attributes)
    // {
    //     $data = null;

    //     if (!empty($attributes) && !is_null($attributes)) {
    //         $data = $this->userRepository->getWhereArray($attributes)->first();
    //     } 

    //     return $data;
    // }


    /**
     * Creates a user
     *
     * Creates a user including all tied tables
     *
     * @param   array   $data            Data to create a user
     * @param   string  $domain_uuid     Domain id where to create user in
     *
     * @return   type  Description
     */
    public function createTODEL(array $data, string $domain_uuid)
    {
        $this->database->beginTransaction();
        $domain = null;
        try {
            // Check if domain exists
            $domain = $this->domainRepository->getWhere('domain_name', $data['domain_name']);

            // We cannot create a user if there is not such a domain
            if ($domain->count() < 1) {
                throw new DomainNotFoundException();
            }

            $domain = $domain->first();

            $users = Arr::get($data, 'users');
            dd($users);

            // Get user by domain and username - create only if there is no a user with such a name
            $user = $this->userRepository->getWhereArray([
                'domain_uuid' => $domain['domain_uuid'],
                'username' => $data['username'],
            ]);

            if ($user->count() > 0) {
                throw new UserExistsException();
            }

            // // Check for the email in the current domain
            // $contact_email = $this->contact_emailRepository->getWhereArray([
            //     'domain_uuid' => $domain['domain_uuid'],
            //     'email_address' => $data['email'],
            // ]);

            // if ($contact_email->count() > 0) {
            //     throw new EmailExistsException();
            // }

            $data['domain_uuid'] = $domain->getAttribute('domain_uuid');

            // Create a contact
            $data['contact_nickname'] = $data['email'];

            $contact = $this->contactRepository->create($data);

            // Hide the field in the output
            $contact->makeHidden(['domain_uuid']);

            // Create a email for the contact
            $data['contact_uuid'] = $contact->getAttribute('contact_uuid');

            $data['email_primary'] = 1;
            $data['email_address'] = $data['email'];

            $contact_email = $this->contact_emailRepository->create($data);

            // Hide the field in the output
            $contact_email->makeHidden(['domain_uuid', 'contact_uuid']);

            // Finally create the user and hide an unneded field in the output
            $data['user_email'] = $data['email'];
            $user = $this->userRepository->create($data);

            $user->makeHidden(['domain_uuid', 'contact_uuid']);

            // Get group name
            $group = $this->groupRepository->getWhere('group_name', $data['group_name']);
            $data['group_uuid'] = $group->first()->group_uuid;

            // Assign the newly created user to the group
            $this->setOneToManyRelations('Groups', $user->user_uuid, [$data['group_uuid']]);

            // Set relations to later output it
            $contact->setRelation('contact_email', $contact_email);
            $user->setRelation('contact', $contact);

            if (is_null($domain)) {
                $domain = $this->domainRepository
                    ->getWhere('domain_uuid', $data['domain_uuid'])
                    ->first();
            }
            $user->setRelation('domain', $domain);

            // Create an extension
            $extension_number = $this->extensionRepository->getWhere('domain_uuid', $data['domain_uuid'])->max('extension');

            if ($extension_number < 100) {
                $extension_number = 100;
            } else {
                $extension_number = (int) $extension_number + 1;
            }

            $password = uniqid();

            $this->extensionService->create(['extension' => $extension_number, 'password' => $password], $user);
            // $extension = $this->extensionService->create(['extension' => $extension_number, 'password' => $password], $user);
            // $extension->makeVisible('password');
            // $this->extensionService->setOneToManyRelations('Users', $extension->extension_uuid, [$user->user_uuid]);
            // $user->setRelation('extension', $extension);

            $this->dispatcher->dispatch(new UserWasCreated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function activate($hash, $sendNotification = true)
    {
        // Since there is no a field dedicated to activation, Gruz have decided to use the quazi-boolean user_enabled field.
        // FusionPBX recognizes non 'true' as FALSE. So our hash in the user_enabled field is treated as FALSE till user is activated.

        // if (!Str::isUuid($hash)) {
        //     throw new ActivationHashWrongException();
        // }

        $this->database->beginTransaction();

        try {
            $user = $this->userRepository->getWhere('user_enabled', $hash)->first();

            if (is_null($user)) {
                throw new ActivationHashNotFoundException();
            }

            $user->user_enabled = 'true';

            $user->save();

            $this->dispatcher->dispatch(new UserWasActivated($user, $sendNotification));

        } catch (Exception $e) {
            $this->database->rollBack();
            throw $e;
        }

        $this->database->commit();

        $response = [
            'message' => __('User activated'),
            'user' => $user
        ];

        return $response;
    }
}
