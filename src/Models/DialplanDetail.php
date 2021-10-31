<?php

namespace Gruz\FPBX\Models;

use Illuminate\Notifications\Notifiable;
use Gruz\FPBX\Models\AbstractModel;

class DialplanDetail extends AbstractModel
{
    use Notifiable;

    public function transferExtension() {
        return $this->hasOne(DialplanDetail::class, 'dialplan_uuid', 'dialplan_uuid')
            ->where([
                ['dialplan_detail_tag', 'action'],
                ['dialplan_detail_type', 'transfer'],
            ]);
    }
}
