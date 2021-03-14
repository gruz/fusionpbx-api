<?php

namespace Infrastructure\Database\Eloquent;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Infrastructure\Traits\Uuids;

abstract class AbstractModel extends BaseModel
{
    use Uuids;

    public static $staticAppends;
    public static $staticHidden;
    public static $staticMakeVisible;
    public static $staticVisible;

    public function __construct(array $attributes = [])
    {
        if (isset(self::$staticAppends)) {
            $this->appends = self::$staticAppends;
        }
        if (isset(self::$staticHidden)) {
            $this->hidden = self::$staticHidden;
        }
        if (isset(self::$staticMakeVisible)) {
            $this->makeVisible(self::$staticMakeVisible);
        }
        if (isset(self::$staticVisible)) {
            $this->visible = self::$staticVisible;
        }

        $className = get_class($this);
        $className = explode('\\', $className);
        $modelName = end($className);
        $stem = Str::snake($modelName);
        $this->table = 'v_' . $stem . 's';
        $this->primaryKey = $stem . '_uuid';
        $this->incrementing = false;
        $this->timestamps = false;

        /**
         * Override model defaults by config file
         */
        $keys = [
            'makeFillable',
            'mergeGuarded',
            'makeVisible',
            'makeHidden',
        ];

        foreach ($keys as $key) {
            $fields = config('fpbx.table.' . $this->table . '.' . $key , []);

            if (!empty($fields)) {
                switch ($key) {
                    case 'mergeGuarded':
                        $fillable = array_diff($this->getFillable(), $fields);
                        $this->fillable($fillable);
                        break;
                    default:
                        break;
                }

                $this->$key($fields);
            }
        }

        /**
         * The "type" of the primary key ID.
         *
         * @var string
         */
        $this->keyType = 'uuid';

        parent::__construct($attributes);
    }

    public function __destruct()
    {
        self::$staticAppends = null;
        self::$staticHidden = null;
        self::$staticVisible = null;
        self::$staticMakeVisible = null;
    }

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
            $table = $this->getTable();

            $tableColumns = $this->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($table);

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

        if (config('fpbx.debug.swaggerProcessor')) {
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
     * Checks if filed is visible
     */
    public function isVisible($field)
    {
        $visible = $this->getVisible();
        $hidden = $this->getHidden();

        if (empty($visible) && empty($hidden)) {
            return true;
        }

        if (in_array($field, $hidden)) {
            return false;
        }

        if (in_array($field, $visible)) {
            return true;
        }

        if (empty($visible)) {
            return true;
        }

        return false;;
    }

    // public function is_nullable(string $field_name)
    // {
    //     if (is_null(static::$_columns_info)) {
    //         static::$_columns_info = $this->getTableColumnsInfo(true);
    //     }

    //     // dd(static::$_columns_info);
    //     $colmns = static::$_columns_info;
    //     if (is_null(static::$_nullable_fields)) {
    //         foreach ($colmns as $fieldName => $fieldData) {
    //             if (!$fieldData->getNotNull()) {
    //                 static::$_nullable_fields[] = $field_name;
    //             }
    //         }
    //     }

    //     return in_array($field_name, static::$_nullable_fields);
    // }

    // public function getDbFieldType($field)
    // {
    //     $fieldType = DB::getSchemaBuilder()->getColumnType($this->getTable(), $field);

    //     return $fieldType;
    // }

}
