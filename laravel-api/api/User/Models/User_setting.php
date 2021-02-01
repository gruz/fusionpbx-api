<?php

namespace Api\User\Models;

use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
 */
class User_setting extends Model
{
    use FusionPBXTableModel;

}
