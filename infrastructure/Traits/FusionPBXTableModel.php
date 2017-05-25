<?php
namespace Infrastructure\Traits;

use Infrastructure\Traits\Uuids;

trait FusionPBXTableModel
{
    use Uuids;

    public static $staticAppends;
    public static $staticHidden;
    public static $staticMakeVisible;
    public static $staticVisible;

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

    }

    public function __destruct()
    {
      self::$staticAppends = null;
      self::$staticHidden = null;
      self::$staticVisible = null;
      self::$staticMakeVisible = null;
    }
}