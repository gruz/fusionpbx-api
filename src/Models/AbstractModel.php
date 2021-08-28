<?php

namespace Gruz\FPBX\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Index;
use Gruz\FPBX\Traits\UuidsTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Gruz\FPBX\Services\FreeSwitchHookService;
use Gruz\FPBX\Exceptions\MissingDomainUuidException;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Contracts\Container\BindingResolutionException;

abstract class AbstractModel extends BaseModel
{
    use UuidsTrait;
    use \Awobaz\Compoships\Compoships;

    // public static $staticAppends;
    // public static $staticHidden;
    public static $staticMakeVisible;
    public static $staticMakeHidden;
    public static $staticSetHidden;
    public static $staticSetVisible;
    // public static $staticVisible;

    private static function staticUpdateFieldsAttributes(&$attrName, $fields, $replace = false) {
        $class = get_called_class();
        if (!$replace) {
            $fieldsOld = Arr::get($attrName, $class, []);
            $fields = array_merge($fieldsOld, $fields);
        }
        $attrName[$class] = $fields;
    }

    /**
     * The function is intended to be called statically to instruct model to overide visible fields
     *
     * E.g. before gettinga user with relations we instruct to show extension password
     * ```
     * \Gruz\FPBX\Models\Extension::staticMakeVisible(['password']);
     * return $this->userRepository->getById(Auth::user()->user_uuid);
     * ```
     * @param array $fields
     * @return void
     */
    public static function staticMakeVisible(array $fields)
    {
        self::staticUpdateFieldsAttributes(static::$staticMakeVisible, $fields);
    }

    /**
     * The function is intended to be called statically to instruct model to overide visible fields
     *
     * E.g. before gettinga user with relations we instruct to hide accountcode
     * ```
     * \Gruz\FPBX\Models\Extension::staticMakeHidden(['accountcode']);
     * return $this->userRepository->getById(Auth::user()->user_uuid);
     * ```
     * @param array $fields
     * @return void
     */
    public static function staticMakeHidden(array $fields)
    {
        self::staticUpdateFieldsAttributes(static::$staticMakeHidden, $fields);
    }

    /**
     * The function is intended to be called statically to instruct model to overide visible fields
     *
     * E.g. before gettinga user with relations we instruct to hide only accountcode and extention
     * ```
     * \Gruz\FPBX\Models\Extension::staticSetHidden(['accountcode', 'extension']);
     * return $this->userRepository->getById(Auth::user()->user_uuid);
     * ```
     * @param array $fields
     * @return void
     */
    public static function staticSetHidden(array $fields)
    {
        self::staticUpdateFieldsAttributes(static::$staticSetHidden, $fields);
    }

    /**
     * The function is intended to be called statically to instruct model to overide visible fields
     *
     * E.g. before gettinga user with relations we instruct to show only extension and password fields
     * ```
     * \Gruz\FPBX\Models\Extension::staticSetVisible(['extension','password']);
     * return $this->userRepository->getById(Auth::user()->user_uuid);
     * ```
     * @param array $fields
     * @return void
     */
    public static function staticSetVisible(array $fields)
    {
        self::staticUpdateFieldsAttributes(static::$staticSetVisible, $fields);
    }

    public function __construct(array $attributes = [])
    {
        $class = get_called_class();
        if (isset(static::$staticMakeVisible)) {
            $fields = Arr::get(static::$staticMakeVisible, $class, []);
            if (!empty($fields)) {
                $this->makeVisible($fields);
            }
        }
        if (isset(static::$staticMakeHidden)) {
            $fields = Arr::get(static::$staticMakeHidden, $class, []);
            if (!empty($fields)) {
                $this->makeHidden($fields);
            }
        }
        if (isset(static::$staticSetHidden)) {
            $fields = Arr::get(static::$staticSetHidden, $class, false);
            if (is_array($fields)) {
                $this->hidden = $fields;
            }
        }
        if (isset(static::$staticSetVisible)) {
            $fields = Arr::get(static::$staticSetVisible, $class, false);
            if (is_array($fields)) {
                $this->visible = $fields;
            }
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
            $fields = config('fpbx.table.' . $this->table . '.' . $key, []);

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
        static::$staticMakeVisible[get_called_class()] = null;
        static::$staticMakeHidden[get_called_class()] = null;
        static::$staticSetHidden[get_called_class()] = null;
        static::$staticSetVisible[get_called_class()] = null;
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
            $result = [];
            $table = $this->getTable();

            $tableColumns = $this->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($table);

            if ($ignoreFillable) {
                $result = $tableColumns;
            } else {
                $fillableProps = $this->getFillable();
                $guardedProps = $this->getGuarded();
                if (!is_null($guardedProps) && !empty($guardedProps)) {
                    $result = array_diff($tableColumns, $guardedProps);
                    // dd($result);
                } else if (!is_null($fillableProps) && !empty($fillableProps)) {
                    $result = array_intersect($tableColumns, $fillableProps);
                } else {
                    $result = $tableColumns;
                }
            }

            return $result;
        };

        if (config('app.debug')) {
            $data = $getData();
        } else {
            $data = Cache::remember(
                __METHOD__ . serialize(func_get_args()) . $this->getTable(),
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
                __METHOD__ . serialize(func_get_args()) . $this->getTable(),
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
                __METHOD__ . serialize(func_get_args()) . $this->getTable(),
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function (AbstractModel $model) {
            $columns = $model->getTableColumnNames(true);

            $primaryKey = $model->getKeyName();

            if ('domain_uuid' !== $primaryKey && in_array('domain_uuid', $columns)) {
                $domain_uuid = $model->getAttribute('domain_uuid');
                if (empty($domain_uuid)) {
                    throw new MissingDomainUuidException;
                }
            }
        });

        static::saved(function (AbstractModel $model) {
            if (!config('disable_fpbx_refresh')) {
                $s = app(FreeSwitchHookService::class)->reload();
            }
        });
    }
}
