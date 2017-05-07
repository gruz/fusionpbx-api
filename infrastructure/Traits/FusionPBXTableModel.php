<?php
namespace Infrastructure\Traits;

use Infrastructure\Traits\Uuids;

trait FusionPBXTableModel
{
    use Uuids;

    public function __construct($attributes = array())
    {
      $file = explode('\\',debug_backtrace()[0]['class']);
      $file = end($file);
      $file = strtolower($file);
      $this->table = 'v_' . $file . 's';
      $this->primaryKey = $file . '_uuid';
      $this->incrementing = false;
      $this->timestamps = false;

      parent::__construct($attributes);
    }
}