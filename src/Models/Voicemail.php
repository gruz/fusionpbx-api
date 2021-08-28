<?php
namespace Gruz\FPBX\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Gruz\FPBX\Models\AbstractModel;

/**
 * @OA\Schema()
 */
class Voicemail extends AbstractModel
{
    use HasFactory;

    protected $guarded = [
        'domain_uuid',
        'voicemail_uuid',
        'voicemail_id',
        'voicemail_name_base64',
    ];

    protected $hidden = [
        'voicemail_password'
    ];
}
