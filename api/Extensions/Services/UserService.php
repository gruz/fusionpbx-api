<?php

namespace Api\Users\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Api\Users\Exceptions\InvalidRoleException;
use Api\Users\Exceptions\UserNotFoundException;
use Api\Users\Events\UserWasCreated;
use Api\Users\Events\UserWasDeleted;
use Api\Users\Events\UserWasUpdated;
use Api\Users\Repositories\RoleRepository;
use Api\Users\Repositories\UserRepository;

class UserService
{
    private $auth;

    private $database;

    private $dispatcher;

    private $roleRepository;

    private $userRepository;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        RoleRepository $roleRepository,
        UserRepository $userRepository
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
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
            $user = $this->userRepository->create($data);

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

    public function addRoles($userId, array $roleIds)
    {
        $user = $this->getRequestedUser($userId, [
            'includes' => ['roles']
        ]);

        $currentRoles = $user->roles->pluck('id')->toArray();
        $roles = $this->checkValidityOfRoles($roleIds);

        $this->userRepository->setRoles($user, $roleIds);

        $roles
            ->filter(function ($role) use ($currentRoles) {
                return !in_array($role->id, $currentRoles);
            })
            ->each(function ($role) use ($user) {
                $user->roles->add($role);
            });

        return $user;
    }

    public function setRoles($userId, array $roleIds)
    {
        $user = $this->getRequestedUser($userId, [
            'includes' => ['roles']
        ]);

        $currentRoles = $user->roles->pluck('id')->toArray();
        $roles = $this->checkValidityOfRoles($roleIds);

        $remove = array_diff($currentRoles, $roleIds);
        $add = array_diff($roleIds, $currentRoles);

        $this->userRepository->setRoles($user, $add, $remove);

        $user->setRelation('roles', new Collection($roles));

        return $user;
    }

    public function removeRoles($userId, array $roleIds)
    {
        $user = $this->getRequestedUser($userId, [
            'includes' => ['roles']
        ]);

        $roles = $this->checkValidityOfRoles($roleIds);

        $this->userRepository->setRoles($user, [], $roleIds);

        $updatedRoleCollection = $user->roles->filter(function ($role) use ($roleIds) {
            return !in_array($role->id, $roleIds);
        });
        $user->setRelation('roles', $updatedRoleCollection);

        return $user;
    }

    private function checkValidityOfRoles(array $roleIds = [])
    {
        $roles = $this->roleRepository->getWhereIn('id', $roleIds);

        if (count($roleIds) !== $roles->count()) {
            $missing = array_diff($roleIds, $roles->pluck('id')->toArray());
            throw new InvalidRoleException($missing[0]);
        }

        return $roles;
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
