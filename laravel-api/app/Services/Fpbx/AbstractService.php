<?php

namespace App\Services\Fpbx;

use Exception;
use App\Models\AbstractModel;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use App\Repositories\AbstractRepository;
use Illuminate\Database\DatabaseManager;
use App\Exceptions\UserNotFoundException;

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
        $className = $this->getBaseClassName('Repositories', 'Repository');

        if (class_exists($className)) {
            $object =  app($className);
            return $object;
        }

        return null;
    }

    protected function getEventClassName($action)
    {
        $className = $this->getBaseClassName('Events', 'Was' . $action);

        return $className;
    }

    protected function throwNotFoundException()
    {
        $className = $this->getBaseClassName('Exceptions', 'NotFoundException');

        throw new $className;
    }

    private function getBaseClassName($replace, $suffix)
    {
        preg_match('/.*\\\\(.*)Service$/', get_class($this), $matches);
        $entity = $matches[1];
        $className = 'App\\' . $replace . '\\' . $entity . $suffix;

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
    protected function dispatchEvent($action, $data, $options = [])
    {
        $eventClassName = $this->getEventClassName($action);

        if (class_exists($eventClassName)) {
            $this->dispatcher->dispatch(new $eventClassName($data, $options));
        }
    }

    public function create($data, $options = [])
    {
        $this->database->beginTransaction();

        try {
            $model = $this->repository->create($data, $options);

            $this->dispatchEvent('Created', $model, $options);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $model;
    }

    public function createMany($data, $options = [])
    {
        $this->database->beginTransaction();

        $models = [];

        try {
            foreach ($data as $key => $row) {
                $model = $this->create($row, $options);
                // $model = $this->repository->create($row, $options);
                // $this->dispatchEvent('Created', $model, $options);
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

    public function update($id, array $data, $options = [])
    {
        $model = $this->getById($id);

        $this->database->beginTransaction();

        try {
            $this->repository->update($model, $data);

            $this->dispatchEvent('Updated', $model, $options);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $model;
    }

    public function delete($id, $options = [])
    {
        $model = $this->getById($id);

        $this->database->beginTransaction();

        try {
            $this->repository->delete($model);

            $this->dispatchEvent('Deleted', $model, $options);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    public function createAttachedMany(AbstractModel $parentModel, string $childRepositoryClassName, array $childData, string $pivotRepositoryClassName, $options = [])
    {
        $this->database->beginTransaction();

        try {
            $this->repository->createAttachedMany($parentModel, $childRepositoryClassName, $childData, $pivotRepositoryClassName, $options);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    public function getByAttributes(array $attributes, $options = [])
    {
        $data = null;

        if (!empty($attributes) && !is_null($attributes)) {
            $data = $this->repository->getWhereArray($attributes, $options);
        }

        return $data;
    }

    public function getByAttributeValues($attribute, array $values)
    {
        $data = null;

        if (
            !empty($attribute) && !is_null($attribute) &&
            !empty($values) && !is_null($values) && is_array($values)
        ) {
            $data = $this->repository->getWhereIn($attribute, $values)->toArray();
        }

        return $data;
    }

    public function setRelation(AbstractModel $parent, AbstractModel $child, $options = []) {
        return $this->repository->setRelation($parent, $child, $options);
    }


    protected function injectData($data, $inject)
    {
        foreach ($data as $key => $row) {
            $data[$key] = array_merge($inject, $row);
        }

        return $data;
    }
}
