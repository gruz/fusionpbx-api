<?php

namespace App\Database\Eloquent;

use Optimus\Genie\Repository as BaseRepository;

abstract class Repository extends BaseRepository
{
  public function setOrdering($property, $direction = 'ASC')
  {
    $this->sortProperty = $property;
    $this->sortDirection = $property;

    return $this;
  }


}
