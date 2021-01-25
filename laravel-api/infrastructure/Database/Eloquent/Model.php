<?php

namespace Infrastructure\Database\Eloquent;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;

abstract class Model extends BaseModel
{
    // protected static $_columns_info = NULL;
    // protected static $_nullable_fields = NULL;

    /**
     * Gets column names from table associated with current model
     *
     * @param bool $ignoreFillable If to get all columns or fillable only
     * @return array
     * @throws BindingResolutionException
     */
    public function getTableColumnNames($ignoreFillable = false)
    {
        $getData = function () use ($ignoreFillable) {
            $tableColumns = $this->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($this->getTable());

            if ($ignoreFillable) {
                $result = $tableColumns;
            } else {
                $fillableProps = $this->getFillable();
                $result = array_intersect($tableColumns, $fillableProps);
            }

            return $result;
        };

        if (config('app.debug')) {
            $data = $getData();
        } else {
            $data = Cache::remember(
                __METHOD__ . serialize(func_get_args()),
                now()->addDay(),
                $getData
            );
        }

        return $data;
    }

    /**
     * Gets columns info from table associated with current model
     * [name => [info]] array.
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumnsInfo($ignoreFillable = false)
    {
        $getData = function () use ($ignoreFillable) {
            $columns_info = [];
            $column_names = $this->getTableColumnNames($ignoreFillable);

            foreach ($column_names as $column_name) {
                $info = $this->getConnection()
                    ->getDoctrineColumn($this->getTable(), $column_name);
                $columns_info[$column_name] = $info;
            }

            return $columns_info;
        };

        if (config('app.debug')) {
            $data = $getData();
        } else {
            $data = Cache::remember(
                __METHOD__ . serialize(func_get_args()),
                now()->addDay(),
                $getData
            );
        }

        return $data;
    }

    /**
     * Gets unique constarints from table columns
     *
     * @param string|null $tableName
     * @return \Doctrine\DBAL\Schema\Identifier[] Column names which has unique constarints in db table
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function getUniqueColumnsFromTable($tableName = null)
    {

        if (empty($tableName)) {
            $tableName = $this->getTable();
        }

        $getData = function () use ($tableName) {
            /**
             * @var Index[]
             */
            $indexColumns = DB::getDoctrineSchemaManager()
                ->listTableIndexes($tableName);
            $uniqueColumns = [];

            /**
             * @var Index $index
             */
            foreach ($indexColumns as $index) {
                if ($index->isUnique() || $index->isPrimary()) {
                    $uniqueColumns = array_merge($uniqueColumns, $index->getColumns());
                }
            }
            $uniqueColumns = array_unique($uniqueColumns);

            return $uniqueColumns;
        };

        if (config('app.debug')) {
            $data = $getData();
        } else {
            $data = Cache::remember(
                __METHOD__ . serialize(func_get_args()),
                now()->addDay(),
                $getData
            );
        }

        return $data;
    }

    /**
     * Get fillable model properties
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    // public function is_nullable(string $field_name)
    // {
    //     if (is_null(static::$_columns_info) ){
    //         static::$_columns_info = $this->getTableColumnsInfo(true);
    //         // static::$_columns_info = DB::table($db_name.'.columns')
    //         //     ->where('table_name',$this->getTable())
    //         //     ->get();
    //     }
    //     $colmns = static::$_columns_info;
    //     if (is_null(static::$_nullable_fields) ){
    //         foreach ($colmns as $fieldName => $fieldData) {}
    //         static::$_nullable_fields = array_map(
    //                 function ($fld){return $fld['notnull'];},
    //                 $colmns
    //         );
    //     }

    //     return in_array($field_name, static::$_nullable_fields);
    // }

    // public function getDbFieldType($field)
    // {
    //     $fieldType = DB::getSchemaBuilder()->getColumnType($this->getTable(), $field);

    //     return $fieldType;
    // }
}
