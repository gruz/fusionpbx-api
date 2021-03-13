<?php
namespace Api\Voicemail\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Infrastructure\Database\Eloquent\Model;

/**
 * @OA\Schema()
 */
class Voicemail extends Model
{
    use HasFactory;

    protected $guarded = [
        'domain_uuid',
        'voicemail_uuid',
        'voicemail_id',
        'voicemail_name_base64',
    ];
}
