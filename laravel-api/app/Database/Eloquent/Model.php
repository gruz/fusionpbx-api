<?php

namespace App\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Schema\Index;

abstract class Model extends BaseModel
{
    // protected static $_columns_info = NULL;
    // protected static $_nullable_fields = NULL;

     /**
     * Gets column names from table associated with current model
     * 
     * @return array 
     */
    public function getTableColumnNames() 
    {
        $tableColumns = $this->getConnection()
                             ->getSchemaBuilder()
                             ->getColumnListing($this->getTable());
        
        $fillableProps = $this->getFillable();
        $result = array_intersect($tableColumns, $fillableProps);

        return $result;
    }

    /**
     * Gets columns info from table associated with current model 
     * [name => [info]] array.
     * 
     * @return array
     */
    public function getTableColumnsInfo()
    {
        $columns_info = [];
        $column_names = $this->getTableColumnNames();
        foreach ($column_names as $column_name) {
            $info = $this->getConnection()
                         ->getDoctrineColumn($this->getTable(), $column_name);
            $columns_info[$column_name] = $info;
        }

        return $columns_info;
    }

/**
     * Gets unique constarints from table columns
     * 
     * @return array Column names which has unique constarints in db table
     */
    public function getUniqueColumnsFromTable($tableName) 
    {
        /**
         * @var Index[]
         */
        $indexColumns = DB::getDoctrineSchemaManager()
                       ->listTableIndexes($tableName);
        $uniqueColumnsText = "";

        /**
        * @var Index $index
        */
        foreach ($indexColumns as $index) {
            if ($index->isUnique() && !$index->isPrimary()){
                $uniqueIndexColumns = implode(" ", $index->getColumns());
                $uniqueColumnsText .= $uniqueIndexColumns . " ";
            }
        }
        $uniqueColumns = array_unique(explode(" ", trim($uniqueColumnsText)));

        return $uniqueColumns;
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
