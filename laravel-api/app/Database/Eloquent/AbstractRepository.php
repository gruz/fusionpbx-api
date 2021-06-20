<?php

namespace App\Database\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Optimus\Genie\Repository as BaseRepository;
use App\Database\Eloquent\AbstractModel;

abstract class AbstractRepository extends BaseRepository
{
    /**
     * Sets $this->database variable to DB::getFacadeRoot()
     *
     * At one hand it's a legacy code workaround since constructor is `final` in Optimus\Genie\Repository
     * On the other hand we simulate constructor here to quickly replace $this->database object on all repositories.
     *
     * @param string $str
     * @return mixed
     */
    public function __get($str)
    {
        switch ($str) {
            case 'database':
                return \Illuminate\Support\Facades\DB::getFacadeRoot();
                break;

            default:
                return;
                break;
        }
    }
    public function setOrdering($property, $direction = 'ASC')
    {
        $this->sortProperty = $property;
        $this->sortDirection = $property;

        return $this;
    }

    public function createMany(array $data, $options = [])
    {
        $model = $this->getModel();

        $primaryKey = $model->getKeyName();

        $forceFields = Arr::get($options, 'forceFillable', []);

        foreach ($data as $key => $row) {
            foreach ($row as $k => $v) {
                if (in_array($k, $forceFields)) {
                    continue;
                }
                if (!$model->isFillable($k)) {
                    unset($data[$key][$k]);
                    continue;
                }
            }
            if (!array_key_exists($primaryKey, $row)) {
                $data[$key][$primaryKey] = Str::uuid()->toString();
            }
        }

        $models = [];

        foreach ($data as $key => $row) {
            $model = $model->newInstance();

            foreach ($row as $key => $value) {
                $model->$key = $value;
            }
            $model->save();
            // $model->create($row, $options);
            $models[] = $model;
        }
        // $model->insert($data);

        return $models;
    }

    public function create(array $data, $options = [])
    {
        $model = $this->getModel();

        $model->fill($data);

        $forceFields = Arr::get($options, 'forceFillable', []);

        foreach ($forceFields as $fieldName) {
            if (array_key_exists($fieldName, $data)) {
                $model->$fieldName = $data[$fieldName];
            }
        }

        $model->save();

        return $model;
    }

    public function update(AbstractModel $model, array $data)
    {
        $model->fill($data);

        $model->save();

        return $model;
    }

    /**
     * @return Model
     */
    protected function getModel()
    {
        preg_match('~.*\\\\(.*)Repository$~', get_class($this), $matches);

        $className = 'App\\Models\\' . $matches[1];

        $model =  new $className();

        return $model;
    }

    public function createAttachedMany(AbstractModel $parentModel, string $childRepositoryClassName, array $childData, string $pivotRepositoryClassName, $options = [])
    {
        /**
         * @var AbstractRepository
         */
        $childRepository = app($childRepositoryClassName);

        /**
         * @var AbstractRepository
         */
        $pivotRepository = app($pivotRepositoryClassName);

        $pivotModel = $pivotRepository->getModel();

        $modelsRelated = $childRepository->createMany($childData, $options);

        $pivotArr = [
            'domain_uuid' => $parentModel->domain_uuid,
            $parentModel->getKeyName() => $parentModel->getKey(),
        ];

        $pivotData = [];
        foreach ($modelsRelated as $v => $modelRelated) {
            $pivotData[] = array_merge(
                $pivotArr,
                [
                    $pivotModel->getKeyName() => Str::uuid()->toString(),
                    $modelRelated->getKeyName() => $modelRelated->getKey(),
                ]
            );
        }

        $pivotRepository->createMany($pivotData, $options);
    }

    public function setRelation(AbstractModel $parent, AbstractModel $child, $options = []) {
        $relationName = explode('v_', $child->getTable(), 2);
        $relationName = end($relationName);

        $table = $parent->$relationName()->getTable();
        preg_match('~^v_(.*)s$~', $table, $m);
        $pivotName = $m[1] . '_uuid';

        $options = array_merge([
            $pivotName => Str::uuid()->toString(),
            'domain_uuid' => $parent->domain_uuid,
        ], $options);

        $parent->$relationName()->save($child, $options);
    }
}
