<?php

namespace Infrastructure\Database\Eloquent;

use Illuminate\Support\Str;
use Infrastructure\Database\Eloquent\Model;
use Optimus\Genie\Repository as BaseRepository;

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

    public function createMany(array $data)
    {
        $model = $this->getModel();

        $primaryKey = $model->getKeyName();

        foreach ($data as $key => $value) {
            foreach ($value as $k => $v) {
                if (!$model->isFillable($k)) {
                    unset($data[$key][$k]);
                    continue;
                }
            }
            if (!array_key_exists($primaryKey, $value)) {
                $data[$key][$primaryKey] = Str::uuid();
            }
        }

        $models = [];

        foreach ($data as $key => $row) {
            $model->create($row);
            $models[] = $model;
        }
        // $model->insert($data);

        return $models;
    }

    public function create(array $data)
    {
        $model = $this->getModel();

        $model->fill($data);

        $model->save();

        return $model;
    }

    public function update(Model $model, array $data)
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
        $className = substr(get_class($this), 0, -1 * strlen('Repository'));
        $className = explode('\\', $className);
        $className = array_diff($className, ['Repositories']);
        $modelName = array_pop($className);
        $className[] = 'Models';
        $className[] = $modelName;

        $className = implode('\\', $className);

        $model =  new $className();

        return $model;
    }

    public function createAttachedMany(Model $parentModel, string $childRepositoryClassName, array $childData, string $pivotRepositoryClassName)
    {
        /**
         * @var Repository
         */
        $childRepository = app($childRepositoryClassName);

        /**
         * @var Repository
         */
        $pivotRepository = app($pivotRepositoryClassName);

        $pivotModel = $pivotRepository->getModel();

        $modelsRelated = $childRepository->createMany($childData);

        $pivotArr = [
            'domain_uuid' => $parentModel->domain_uuid,
            $parentModel->getKeyName() => $parentModel->getKey(),
        ];

        $pivotData = [];
        foreach ($modelsRelated as $v => $modelRelated) {
            $pivotData[] = array_merge(
                $pivotArr,
                [
                    $pivotModel->getKeyName() => Str::uuid(),
                    $modelRelated->getKeyName() => $modelRelated->getKey(),
                ]
            );
        }

        $pivotRepository->createMany($pivotData);
    }
}
