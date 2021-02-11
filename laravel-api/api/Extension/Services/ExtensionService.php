<?php

namespace Api\Extension\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use Infrastructure\Traits\OneToManyRelationCRUD;
use Illuminate\Database\DatabaseManager;
use Api\User\Repositories\UserRepository;
use Api\Extension\Events\ExtensionWasCreated;
use Api\Extension\Events\ExtensionWasDeleted;
use Api\Extension\Events\ExtensionWasUpdated;
use Api\Extension\Repositories\ExtensionRepository;
use Api\Extension\Exceptions\ExtensionExistsException;
use Api\Extension\Repositories\Extension_userRepository;

class ExtensionService
{
    use OneToManyRelationCRUD;

    private $auth;

    private $database;

    private $dispatcher;

    // ~ private $roleRepository;

    private $extensionRepository;
    private $extension_userRepository;

    private $userRepository;

    private $scope;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        // ~ GroupRepository $roleRepository,
        Extension_userRepository $extension_userRepository,
        UserRepository $userRepository,
        ExtensionRepository $extensionRepository
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        // ~ $this->roleRepository = $roleRepository;
        $this->extensionRepository = $extensionRepository;
        $this->extension_userRepository = $extension_userRepository;
        $this->userRepository = $userRepository;

        $this->setScope();
    }

    public function getAll($options = [])
    {
        $user = $this->auth->user();

        return $this->extensionRepository->getWhereArray(['domain_uuid' => $user->domain_uuid]);
    }

    public function getById($extensionId, array $options = [])
    {
        $extension = $this->getRequestedExtension($extensionId);

        return $extension;
    }

    public function create($data, $user = null)
    {
        $this->database->beginTransaction();

        try {
            if (empty($user)) {
                $user = $this->auth->user();
            }

            if ($this->extensionRepository->getWhereArray(['domain_uuid' => $user->domain_uuid, 'extension' => $data['extension']])->count() > 0) {
                throw new ExtensionExistsException(['domain_name' => $user->domain->domain_name, 'extension' => $data['extension']]);
            }

            // TODO Check if context is passed if it exists at all.
            // TODO check permissions as accountcode must not be set by non-superadmins
            $var = 'accountcode';
            $data[$var] = empty($data[$var]) ? $user->domain->domain_name : $data[$var];
            $var = 'user_context';
            $data[$var] = empty($data[$var]) ? $user->domain->domain_name : $data[$var];

            $var = 'domain_uuid';
            $data[$var] = Arr::get($data, $var, $user->$var);

            $var = 'user_uuid';
            $data[$var] = Arr::get($data, $var, $user->$var);

            $extension = $this->extensionRepository->create($data);
            $this->setOneToManyRelations('Users', $extension->extension_uuid, [$user->user_uuid]);
            $user->setRelation('extension', $extension);


            $cacheURI = "directory:" . $extension->extension . "@" . $extension->user_context;
            $this->dispatcher->dispatch(new ExtensionWasCreated($extension, $cacheURI));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $extension;
    }

    public function update($extensionId, array $data)
    {
        /**
         * @var \Api\Extension\Models\Extension
         */
        $extension = $this->getRequestedExtension($extensionId);
        $user = $this->auth->user();

        $this->database->beginTransaction();

        try {
            $this->extensionRepository->update($extension, $data);

            $this->dispatcher->dispatch(new ExtensionWasUpdated($extension));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $extension;
    }

    public function delete($extensionId)
    {
        $extension = $this->getRequestedExtension($extensionId);

        $this->database->beginTransaction();

        try {
            $this->extensionRepository->delete($extensionId);

            $this->dispatcher->dispatch(new ExtensionWasDeleted($extension));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
