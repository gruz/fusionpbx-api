<?php

namespace Gruz\FPBX\Services\Fpbx;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use Gruz\FPBX\Models\AbstractModel;
use Illuminate\Database\DatabaseManager;
use Gruz\FPBX\Repositories\AbstractRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    private function getBaseClassName($replace, $suffix)
    {
        preg_match('/.*\\\\(.*)Service$/', get_class($this), $matches);
        $entity = $matches[1];
        $className = 'Gruz\FPBX\\' . $replace . '\\' . $entity . $suffix;

        return $className;
    }

    protected function getRequestedUser($userId, array $options = [])
    {
        $user = $this->userRepository->getById($userId, $options);

        if (is_null($user)) {
            throw new  NotFoundHttpException(__(':entity not found', [ 'entity' => 'User']));
        }

        return $user;
    }

    public function getAll($options = [])
    {
        return $this->repository->get($options);
    }

    public function getById($id, array $options = []) // : \Illuminate\Database\Eloquent\Model
    {
        $model = $this->repository->getById($id, $options);

        if (is_null($model)) {
            preg_match('/.*\\\\(.*)Service$/', get_class($this), $matches);
            $entity = $matches[1];
            throw new  NotFoundHttpException(__(':entity not found', [ 'entity' => $entity]));
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
        $this->fpbxRefreshBeginTransaction();

        $callerName = debug_backtrace()[1]['function'];

        if ($callerName !== 'createMany') {
            $this->database->beginTransaction();
        }

        try {
            $model = $this->repository->create($data, $options);

            if (Arr::get($options, 'dispatchDefaultEvent', true)) {
                $this->dispatchEvent('Created', $model, $options);
            }
        } catch (Exception $e) {
            if ($callerName !== 'createMany') {
                $this->database->rollBack();
            }

            throw $e;
        }

        if ($callerName !== 'createMany') {
            $this->database->commit();
        }

        $this->fpbxRefreshEndTransaction();

        return $model;
    }

    public function createMany($data, $options = [])
    {
        $this->fpbxRefreshBeginTransaction();
        $models = [];

        $this->database->beginTransaction();

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

        $this->fpbxRefreshEndTransaction();

        return $models;
    }

    public function update($id, array $data, $options = [])
    {
        $this->fpbxRefreshBeginTransaction();

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

        $this->fpbxRefreshEndTransaction();

        return $model;
    }

    public function delete($id, $options = [])
    {
        $this->fpbxRefreshBeginTransaction();

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

        $this->fpbxRefreshEndTransaction();
    }

    public function createAttachedMany(AbstractModel $parentModel, string $childRepositoryClassName, array $childData, string $pivotRepositoryClassName, $options = [])
    {
        $this->fpbxRefreshBeginTransaction();

        $this->database->beginTransaction();

        try {
            $this->repository->createAttachedMany($parentModel, $childRepositoryClassName, $childData, $pivotRepositoryClassName, $options);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        $this->fpbxRefreshEndTransaction();
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
        $this->fpbxRefreshBeginTransaction();

        $result = $this->repository->setRelation($parent, $child, $options);

        $this->fpbxRefreshEndTransaction();

        return $result;
    }


    protected function injectData($data, $inject)
    {
        foreach ($data as $key => $row) {
            $data[$key] = array_merge($inject, $row);
        }

        return $data;
    }

    protected $refreshDisabled;

    protected function fpbxRefreshBeginTransaction() {
        $this->refreshDisabled = config('disable_fpbx_refresh');

        if (!$this->refreshDisabled) {
            config(['disable_fpbx_refresh' => true]);
        }
    }


    protected function fpbxRefreshEndTransaction()
    {
        if (!$this->refreshDisabled) {
            config(['disable_fpbx_refresh' => false]);
            app(\Gruz\FPBX\Services\FreeSwitchHookService::class)->reload();
        }
    }
}
