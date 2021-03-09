<?php

namespace Api\Dialplan\Repositories;

use Api\Dialplan\Exceptions\CouldNotInjectDialplanException;
use Infrastructure\Database\Eloquent\Repository;

class DialplanRepository extends Repository
{
    /**
     * Load custom dialplan
     *
     * Instead of altering tables manually, let's use native FusionPBX
     * option to load all dialplans in
     * {folder a}/{folder b}/resources/switch/conf/dialplan/889_my_dilaplan.xml
     *
     * @param   type  $name  Description
     *
     * @return   type  Description
     */
    public function createDefaultDialplanRules()
    {
        $dialplan_dest_folder = config('app.fpath_document_root') . '/opt-laravel-api';

        if (is_dir($dialplan_dest_folder)) {
            return;
        }

        $dialplan_storage = $pemFile = base_path() . '/resources/fusionpbx';

        $cmd = 'cp -r ' . $dialplan_storage . ' ' . $dialplan_dest_folder;
        exec($cmd, $output);

        if (!is_dir($dialplan_dest_folder)) {
            throw new CouldNotInjectDialplanException();
        }
    }
}
