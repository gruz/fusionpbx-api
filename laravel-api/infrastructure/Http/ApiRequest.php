<?php

namespace Infrastructure\Http;

use Infrastructure\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Str;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Illuminate\Validation\Rule;

abstract class ApiRequest extends FormRequest
{

    public $mapping = [
        "decimal" => "numeric",
        "text" => "string",
        "guid" => "uuid",
    ];

    /**
     * @var Model $model
     *  
     * Generates basic validation rules based on db table column types.
     * */    
    protected function buildDefaultRules(Model $model) 
    {
        $rules = [];
        $modelClass = get_class($model);
        $modelName = Str::lower(class_basename($modelClass));
        $columns = $model->getTableColumnsInfo();
        $uniqueColumns = $model->getUniqueColumnsFromTable($model->getTable());

        /**
         * @var Column $column
         */
        foreach ($columns as $column) {
            $columnName = $column->getName();
            
            /**
             * @var Type $columnType
             */
            $columnType = $column->getType();
            $columnTypeName = $columnType->getName();
            $columnIsNotNullType = $column->getNotnull();

            // We should append column name with prefix - API demands
            $prefixedColumnName = $modelName . '.' . $columnName;
            $rules[$prefixedColumnName] = $columnTypeName;
            $rule[] = $this->mapping[$columnTypeName] ?
                      $this->mapping[$columnTypeName] :
                      $columnTypeName;

            // Check if field is required or can be null.
            $rule[] = $columnIsNotNullType ? 'required' : 'nullable';

            // Check if field is unique.
            if (in_array($columnName, $uniqueColumns)) {
                $modelObjectId = request()->route()->parameter('id');
                $modelObject = $modelClass::find($modelObjectId);
                $rule[] = Rule::unique($model->getTable(), $columnName)
                          ->ignore($modelObject)
                          ->__toString();
            }

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
