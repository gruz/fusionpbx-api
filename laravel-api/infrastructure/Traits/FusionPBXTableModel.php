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
        parent::__construct($attributes);

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


        $add_fillable = config('fpbx.table.' . $this->table . '.add_fillable', []);
        $this->fillable = array_merge($this->fillable, $add_fillable);

        $remove_fillable = config('fpbx.table.' . $this->table . '.remove_fillable', []);
        $this->fillable = array_diff($this->fillable, $remove_fillable);

        /**
         * The "type" of the primary key ID.
         *
         * @var string
         */
        $this->keyType = 'uuid';
    }

    public function __destruct()
    {
        self::$staticAppends = null;
        self::$staticHidden = null;
        self::$staticVisible = null;
        self::$staticMakeVisible = null;
    }
}
