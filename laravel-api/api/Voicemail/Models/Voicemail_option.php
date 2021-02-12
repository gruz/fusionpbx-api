<?php

namespace Api\Voicemail\Models;

use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
*/

class Voicemail_option extends Model
{
    use FusionPBXTableModel;
}
