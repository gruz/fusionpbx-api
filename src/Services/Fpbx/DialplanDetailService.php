<?php

namespace Gruz\FPBX\Services\Fpbx;

class DialplanDetailService extends AbstractService
{
    public function getExtensionByDestinationNumber($destination_number)
    {
        $dialplanDetails = $this->getByAttributes([
            'dialplan_detail_tag' => 'condition',
            'dialplan_detail_type' => 'destination_number',
            'dialplan_detail_data' => $destination_number,
            // 'dialplan_detail_enabled' => true,
        ], ['includes' => ['transferExtension']])->first();

        // In this case dialplan is treated as enabled in FPBX when null value is set
        if (!in_array($dialplanDetails->dialplan_detail_enabled, [null, true, 'true'])) {
            return false;
        }

        if ($dialplanDetails) {
            $transferExtension = optional($dialplanDetails->transferExtension)->dialplan_detail_data;
            if ($transferExtension) {
                return (int) $transferExtension;
            }
        }

        return null;
    }
}
