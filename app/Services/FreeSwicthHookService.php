<?php

namespace App\Services;

use Illuminate\Support\Facades\Notification;
use App\Notifications\FPBXFailedNotification;

class FreeSwicthHookService
{
    public function reload()
    {
        $output = null;
        try {
            $cmd = config('fpbx.hook_command');
            exec($cmd, $output);
            if (empty($output)) {
                throw new \Exception("Could not reload FusionPBX cache", 1488);
            }
            foreach ($output as $row) {
                if (strpos($row, 'ERR')) {
                    throw new \Exception("Could not reload FusionPBX cache", 1488);
                }
            }
        } catch (\Exception $th) {
            $mainAdminEmail = config('app.contact_email');

            Notification::route('mail', $mainAdminEmail)
                ->notify(new FPBXFailedNotification($th->getMessage(), $cmd, $output));
    
            \Log::error($th->getMessage(), [ 'commands' => $cmd, 'output' => $output]);
        }
        return $output;
    }
}
