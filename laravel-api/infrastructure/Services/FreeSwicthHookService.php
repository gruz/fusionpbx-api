<?php

namespace Infrastructure\Services;

class FreeSwicthHookService
{
    public function reload()
    {
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
            // throw $th;
        }
        return $output;
    }
}
