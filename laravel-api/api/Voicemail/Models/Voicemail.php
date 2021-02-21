<?php
namespace Api\Voicemail\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
 */
class Voicemail extends Model
{
    use HasFactory;
    use FusionPBXTableModel;

    protected $guarded = [
        'domain_uuid',
        'voicemail_uuid',
        'voicemail_id',
        'voicemail_name_base64',
    ];
}
