<?php

namespace App\Http;

use App\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use \Illuminate\Support\Str;

abstract class ApiRequest extends FormRequest
{

    public $mapping = [
        "decimal" => "numeric",
        "text" => "string",
        "guid" => "uuid",
    ];

    /**
     * 
     *  
     * */    
    protected function buildDefaultRules(Model $model) 
    {
        /**
         * @var Illuminate\Database\Eloquent\Model $model
         */
        $modelName = Str::lower(
                     class_basename(
                     get_class($model)));
        $columns = $model->getTableColumnsInfo(true);
        $rules = [];

        /**
         * @var Doctrine\DBAL\Schema\Column $column
         */
        foreach ($columns as $column) {
            $columnName = $column->getName();
            
            /**
             * @var Doctrine\DBAL\Types\Type $columnType
             */
            $columnType = $column->getType();
            $columnTypeName = $columnType->getName();
            $columnIsNotNullType = $column->getNotnull();

            // We should append column name with prefix if it`s nested 
            // or if it`s same as model name
            // if ($columnName == $modelName) {
            //      $columnName =  $modelName . '.' . $columnName;
            // }

            $prefixedColumnName = $modelName . '.' . $columnName;
            $rules[$prefixedColumnName] = $columnTypeName;
            $rule[] = $this->mapping[$columnTypeName] ?
                      $this->mapping[$columnTypeName] :
                      $columnTypeName;

            // Check if field is required or can be null.
            $rule[] = $columnIsNotNullType ? 'required' : 'nullable';

            $rules[$prefixedColumnName] = implode('|', $rule);
            unset($rule);
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntityHttpException($validator->errors()->toJson());
    }

    protected function failedAuthorization()
    {
        throw new HttpException(403);
    }
}
