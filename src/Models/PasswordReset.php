<?php

namespace Gruz\FPBX\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $primaryKey = 'email';

    public $timestamps = true;

    const UPDATED_AT = null;
}
