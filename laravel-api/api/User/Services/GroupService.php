<?php

namespace Api\User\Services;

use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use App\Exceptions\GroupNotFoundException;
use App\Events\GroupWasCreated;
use App\Events\GroupWasDeleted;
use App\Events\GroupWasUpdated;
use Api\User\Repositories\GroupRepository;

class GroupService
{
    private $database;

    private $dispatcher;

    private $groupRepository;

    public function __construct(
        DatabaseManager $database,
        Dispatcher $dispatcher,
        GroupRepository $groupRepository
    ) {
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->groupRepository = $groupRepository;
    }

    public function getAll($options = [])
    {
        return $this->groupRepository->get($options);
    }

    public function getById($groupId, array $options = [])
    {
        $user = $this->getRequestedGroup($groupId);

        return $user;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $user = $this->groupRepository->create($data);

            $this->dispatcher->dispatch(new GroupWasCreated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function update($groupId, array $data)
    {
        $user = $this->getRequestedGroup($groupId);

        $this->database->beginTransaction();

        try {
            $this->groupRepository->update($user, $data);

            $this->dispatcher->dispatch(new GroupWasUpdated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function delete($groupId)
    {
        $user = $this->getRequestedGroup($groupId);

        $this->database->beginTransaction();

        try {
            $this->groupRepository->delete($groupId);

            $this->dispatcher->dispatch(new GroupWasDeleted($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    private function getRequestedGroup($groupId)
    {
        $user = $this->groupRepository->getById($groupId);

        if (is_null($user)) {
            throw new GroupNotFoundException();
        }

        return $user;
    }
}
