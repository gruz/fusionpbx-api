<?php

namespace Infrastructure\Database\Eloquent;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Api\User\Exceptions\UserNotFoundException;
use Infrastructure\Database\Eloquent\AbstractRepository;

abstract class AbstractService
{

    protected $auth;

    protected $database;

    protected $dispatcher;

    /**
     * @var AbstractRepository
     */
    protected $repository;

    public function __construct()
    {
        $this->auth = app(AuthManager::class);
        $this->database = app(DatabaseManager::class);
        $this->dispatcher = app(Dispatcher::class);
        $this->repository = $this->getRepository();
    }

    protected function getRepository()
    {
        // \Api\Settings\Services\SettingService
        // \Api\Settings\Repositories\SettingRepository
        $className = $this->getBaseClassName('Repositories', 'Repository');

        if (class_exists($className)) {
            $object =  app($className);
            return $object;
        }

        return null;
    }

    protected function getEventClassName($action)
    {
        // \Api\Settings\Services\SettingService
        // \Api\Settings\Events\SettingWasCreated
        $className = $this->getBaseClassName('Events', 'Was' . $action);

        return $className;
    }

    protected function throwNotFoundException()
    {
        // \Api\Settings\Services\SettingService
        // \Api\Domain\Exceptions\DomainNotFoundException
        $className = $this->getBaseClassName('Exceptions', 'NotFoundException');

        throw new $className;
    }

    private function getBaseClassName($replace, $suffix)
    {
        $className = substr(get_class($this), 0, -1 * strlen('Service'));
        $className = explode('\\', $className);
        $className = array_diff($className, ['Services']);
        $lastName  = array_pop($className);
        $className[] = $replace;
        $className[] = $lastName . $suffix;
        $className = implode('\\', $className);

        return $className;
    }


    protected function getRequestedUser($userId, array $options = [])
    {
        $user = $this->userRepository->getById($userId, $options);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function getAll($options = [])
    {
        return $this->repository->get($options);
    }

    public function getById($id, array $options = []): \Illuminate\Database\Eloquent\Model
    {
        $collection = $this->repository->getById($id, $options);
        $model = $this->repository->model;
        dd('Here LOAD DATA TO MODEL IF NEEDED');

        if (is_null($collection)) {
            $this->throwNotFoundException();
        }

        return $model;
    }

    /**
     * Dispatches events if such an event exists
     *
     * @param mixed $action Like `WasCreated`
     * @param mixed $data Data to be sent to the event, usually hydrated Model
     * @return void
     */
    private function dispatchEvent($action, $data)
    {
        $eventClassName = $this->getEventClassName($action);

        if (class_exists($eventClassName)) {
            $this->dispatcher->dispatch(new $eventClassName($data));
        }
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $model = $this->repository->create($data);

            $this->dispatchEvent('Created', $model);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $model;
    }

    public function createMany($data)
    {
        $this->database->beginTransaction();

        $models = [];

        try {
            foreach ($data as $key => $row) {
                $model = $this->repository->create($row);
                $this->dispatchEvent('Created', $model);
                $models[] = $model;
            }
            // $model = $this->repository->createMany($data);

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $models;
    }

    public function update($id, array $data)
    {
        $model = $this->getById($id);

        $this->database->beginTransaction();

        try {
            $this->repository->update($model, $data);

            $this->dispatchEvent('Updated', $model);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $model;
    }

    public function delete($id)
    {
        $model = $this->getById($id);

        $this->database->beginTransaction();

        try {
            $this->repository->delete($model);

            $this->dispatchEvent('Deleted', $model);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    public function createAttachedMany(AbstractModel $parentModel, string $childRepositoryClassName, array $childData, string $pivotRepositoryClassName)
    {
        $this->database->beginTransaction();

        try {
            $this->repository->createAttachedMany($parentModel, $childRepositoryClassName, $childData, $pivotRepositoryClassName);
            // $this->dispatchEvent('Deleted', $model);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
