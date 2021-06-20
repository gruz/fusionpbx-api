<?php
namespace Api\Voicemail\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Database\Eloquent\AbstractModel;

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
}
