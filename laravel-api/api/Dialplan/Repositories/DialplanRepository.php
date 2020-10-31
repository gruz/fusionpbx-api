<?php

namespace Api\Dialplan\Repositories;

use Api\Dialplan\Models\Dialplan;
use Api\Dialplan\Exceptions\CouldNotInjectDialplanException;
use App\Database\Eloquent\Repository;
use Illuminate\Database\Eloquent\Collection;

class DialplanRepository extends Repository
{
    public function getModel()
    {
        return new Dialplan();
    }

    public function create(array $data)
    {
        $model = $this->getModel();

        $model->fill($data);

        $model->save();

        return $model;
    }

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
        $dialplan_dest_folder = env('FUSIONPBX_DOCUMENT_ROOT') . '/opt-laravel-api';

        if (is_dir($dialplan_dest_folder))
        {
          return;
        }

        $dialplan_storage = $pemFile = base_path() . '/resources/fusionpbx';

        exec('ln -s ' . $dialplan_storage . ' ' . $dialplan_dest_folder);

        if (!is_dir($dialplan_dest_folder))
        {
          throw new CouldNotInjectDialplanException();
        }
        /*
        $this->database->beginTransaction($data);

        $pemFile = base_path() . '/' . env('VOIP_PUSH_DIALPLAN_XML', false);

        $dialplan_data = [
          'domain_uuid' => $data['domain_uuid'],
          'dialplan_uuid' => \Uuid::generate(),
          'app_uuid' => \Uuid::generate(),
          'dialplan_context' => $data['domain_name'],
          'dialplan_name' => 'push_notification',
          'dialplan_number' => '[api-created]',
          'dialplan_continue' => 'true',
          'dialplan_order' => 885,
          'dialplan_enabled' => 'true',
          'dialplan_description' => 'Created via API'
        ];

        try {
          $dialplan = $this->create($dialplan_data);

          $dialplan_details_data = [
            [
              'domain_uuid' => $dialplan->domain_uuid,
              'dialplan_uuid' => $dialplan->dialplan_uuid,
              'dialplan_detail_uuid' => \Uuid::generate(),
              'dialplan_detail_tag' => 'action',
              'dialplan_detail_type' => 'export',
              'dialplan_detail_data' => 'dialed_extension=${destination_number}',
              'dialplan_detail_break' => NULL,
              'dialplan_detail_inline' => 'true',
              'dialplan_detail_group' => '0',
              'dialplan_detail_order' => '10'
            ],
            [
              'domain_uuid' => $dialplan->domain_uuid,
              'dialplan_uuid' => $dialplan->dialplan_uuid,
              'dialplan_detail_uuid' => \Uuid::generate(),
              'dialplan_detail_tag' => 'action',
              'dialplan_detail_type' => 'limit',
              'dialplan_detail_data' => 'hash ${domain_name} ${destination_number} ${limit_max} ${limit_destination}',
              'dialplan_detail_break' => NULL,
              'dialplan_detail_inline' => 'false',
              'dialplan_detail_group' => '0',
              'dialplan_detail_order' => '15'
            ],
          ];


          // TODO Check if Remove here works.
            if (count($removeGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->where('user_uuid', $user->user_uuid)
                    ->where('domain_uuid', $user->domain_uuid)
                    ->whereIn('group_uuid', $removeGroups)
                    ->delete();
            }

            if (count($addGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->insert(array_map(function ($groupName, $groupId) use ($user) {
                        return [
                            'group_user_uuid' => \Uuid::generate(),
                            'domain_uuid' => $user->domain_uuid,
                            'group_uuid' => $groupId,
                            'group_name' => $groupName,
                            'user_uuid' => $user->user_uuid
                        ];
                    }, $addGroups, array_keys($addGroups)));
            }
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
        */
    }

}
