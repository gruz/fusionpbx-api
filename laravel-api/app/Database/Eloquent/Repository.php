<?php

namespace App\Database\Eloquent;

use Optimus\Genie\Repository as BaseRepository;

abstract class Repository extends BaseRepository
{
  /**
   * Sets $this->database variable to DB::getFacadeRoot()
   *
   * At one hand it's a legacy code workaround since constructor is `final` in Optimus\Genie\Repository
   * On the other hand we simulate constructor here to quickly replace $this->database object on all repositories.
   *
   * @param string $str
   * @return mixed
   */
  public function __get($str) {
    switch ($str) {
      case 'database':
        return \Illuminate\Support\Facades\DB::getFacadeRoot();
        break;

      default:
        return;
        break;
    }

  }
  public function setOrdering($property, $direction = 'ASC')
  {
    $this->sortProperty = $property;
    $this->sortDirection = $property;

    return $this;
  }
}
