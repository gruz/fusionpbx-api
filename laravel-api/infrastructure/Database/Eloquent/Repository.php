<?php

namespace Infrastructure\Database\Eloquent;

use Infrastructure\Database\Eloquent\Model;
use Optimus\Genie\Repository as BaseRepository;

abstract class Repository extends BaseRepository
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

    public function create(array $data)
    {
        $model = $this->getModel();

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
}
