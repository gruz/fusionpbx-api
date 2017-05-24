<?php

namespace Api\Extensions\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Api\Extensions\Exceptions\InvalidExtensionException;
use Api\Extensions\Exceptions\ExtensionNotFoundException;
use Api\Extensions\Exceptions\ExtensionExistsException;
use Api\Extensions\Events\ExtensionWasCreated;
use Api\Extensions\Events\ExtensionWasDeleted;
use Api\Extensions\Events\ExtensionWasUpdated;
use Api\Extensions\Repositories\ExtensionRepository;

class ExtensionService
{
    private $auth;

    private $database;

    private $dispatcher;

    // ~ private $roleRepository;

    private $extensionRepository;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        // ~ GroupRepository $roleRepository,
        ExtensionRepository $extensionRepository
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        // ~ $this->roleRepository = $roleRepository;
        $this->extensionRepository = $extensionRepository;
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
            if (empty($user))
            {
              $user = $this->auth->user();
            }

            if ($this->extensionRepository->getWhereArray(['domain_uuid' => $user->domain_uuid,'extension' => $data['extension']])->count() > 0)
            {
              throw new ExtensionExistsException(['domain_name' => $user->domain->domain_name, 'extension' => $data['extension']]);
            }

            // TODO Check if context is passed if it exists at all.
            // TODO check permissions as accountcode must not be set by non-superadmins
            $var = 'accountcode';
            $data[$var] = empty($data[$var]) ? $user->domain->domain_name : $data[$var];
            $var = 'user_context';
            $data[$var] = empty($data[$var]) ? $user->domain->domain_name : $data[$var];
            $var = 'domain_uuid';
            $data[$var] = empty($data[$var]) ? $user->domain_uuid : $data[$var];

            $extension = $this->extensionRepository->create($data);

            $cacheURI = "directory:" . $extension->extension . "@" . $extension->user_context;
            $this->dispatcher->fire(new ExtensionWasCreated($extension, $cacheURI));

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $extension;
    }

    public function update($extensionId, array $data)
    {
        $extension = $this->getRequestedExtension($extensionId);

        $this->database->beginTransaction();

        try {
            $this->extensionRepository->update($extension, $data);

            $this->dispatcher->fire(new ExtensionWasUpdated($extension));
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

            $this->dispatcher->fire(new ExtensionWasDeleted($extension));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    public function setUsers($extensionId, array $users)
    {
        $extension = $this->getRequestedUser($userId, [
            'includes' => ['groups']
        ]);

        $currentUsers = $extensions->extension_users->pluck('user_uuid')->toArray();
        $groups = $this->checkValidityOfUsers($groupIds);

        $remove = array_diff($currentGroups, $groupIds);
        $add = array_diff($groupIds, $currentGroups);

        $remove = $this->mapGroupNamesToGroupIds($remove);
        $add = $this->mapGroupNamesToGroupIds($add);

        $this->userRepository->setGroups($user, $add, $remove);

        $user->setRelation('groups', new Collection($groups));

        return $user;
    }

    private function checkValidityOfGroups(array $groupIds = [])
    {
        $groups = $this->groupRepository->getWhereIn('group_uuid', $groupIds);

        if (count($groupIds) !== $groups->count()) {
            $missing = array_diff($groupIds, $groups->pluck('group_uuid')->toArray());
            throw new InvalidGroupException(['groupId' => $missing[0]]);
        }

        return $groups;
    }

}
