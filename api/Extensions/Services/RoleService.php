<?php

namespace Api\Users\Services;

use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Api\Users\Exceptions\RoleNotFoundException;
use Api\Users\Events\RoleWasCreated;
use Api\Users\Events\RoleWasDeleted;
use Api\Users\Events\RoleWasUpdated;
use Api\Users\Repositories\RoleRepository;

class RoleService
{
    private $database;

    private $dispatcher;

    private $roleRepository;

    public function __construct(
        DatabaseManager $database,
        Dispatcher $dispatcher,
        RoleRepository $roleRepository
    ) {
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->roleRepository = $roleRepository;
    }

    public function getAll($options = [])
    {
        return $this->roleRepository->get($options);
    }

    public function getById($roleId, array $options = [])
    {
        $user = $this->getRequestedRole($roleId);

        return $user;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $user = $this->roleRepository->create($data);

            $this->dispatcher->fire(new RoleWasCreated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function update($roleId, array $data)
    {
        $user = $this->getRequestedRole($roleId);

        $this->database->beginTransaction();

        try {
            $this->roleRepository->update($user, $data);

            $this->dispatcher->fire(new RoleWasUpdated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function delete($roleId)
    {
        $user = $this->getRequestedRole($roleId);

        $this->database->beginTransaction();

        try {
            $this->roleRepository->delete($roleId);

            $this->dispatcher->fire(new RoleWasDeleted($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    private function getRequestedRole($roleId)
    {
        $user = $this->roleRepository->getById($roleId);

        if (is_null($user)) {
            throw new RoleNotFoundException();
        }

        return $user;
    }
}
