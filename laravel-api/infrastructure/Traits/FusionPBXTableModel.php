<?php

namespace Infrastructure\Traits;

use Infrastructure\Traits\Uuids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait FusionPBXTableModel
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

        $file = explode('\\', debug_backtrace()[0]['class']);
        $file = end($file);
        $file = strtolower($file);
        $this->table = 'v_' . $file . 's';
        $this->primaryKey = $file . '_uuid';
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
}
