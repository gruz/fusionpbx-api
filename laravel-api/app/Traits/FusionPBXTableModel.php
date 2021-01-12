<?php
namespace App\Traits;

use App\Traits\Uuids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait FusionPBXTableModel
{
    use Uuids;

    public static $staticAppends;
    public static $staticHidden;
    public static $staticMakeVisible;
    public static $staticVisible;
    // protected static $_columns_info = NULL;
    // protected static $_nullable_fields = NULL;

    public function __construct($attributes = array())
    {
      parent::__construct($attributes);

      if (isset(self::$staticAppends)){
          $this->appends = self::$staticAppends;
      }
      if (isset(self::$staticHidden)){
          $this->hidden = self::$staticHidden;
      }
      if (isset(self::$staticMakeVisible)){
          $this->makeVisible(self::$staticMakeVisible);
      }
      if (isset(self::$staticVisible)){
          $this->visible = self::$staticVisible;
      }

      $file = explode('\\',debug_backtrace()[0]['class']);
      $file = end($file);
      $file = strtolower($file);
      $this->table = 'v_' . $file . 's';
      $this->primaryKey = $file . '_uuid';
      $this->incrementing = false;
      $this->timestamps = false;

        /**
         * The "type" of the primary key ID.
         *
         * @var string
         */
        $this->keyType = 'uuid';

    }

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

    public function __destruct()
    {
      self::$staticAppends = null;
      self::$staticHidden = null;
      self::$staticVisible = null;
      self::$staticMakeVisible = null;
    }
}