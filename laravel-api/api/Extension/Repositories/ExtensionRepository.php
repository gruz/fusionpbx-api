<?php

namespace Api\Extension\Repositories;

use Illuminate\Support\Str;
use Api\Extension\Models\Extension;
use Infrastructure\Database\Eloquent\AbstractRepository;

class ExtensionRepository extends AbstractRepository
{
    public function getMaxExtension($domain_uuid) {
        $extension = $this->model->where('domain_uuid', $domain_uuid)->selectRaw('max("extension"::integer)')->first()->max;  
        return $extension;
    }

    public function setUsers(Extension $extension, array $addUsers, array $removeUsers = [])
    {

        $this->database->beginTransaction();

        try {
            if (count($removeUsers) > 0) {
                $query = $this->database->table($extension->users()->getTable());
                $query
                    ->where('extension_uuid', $extension->extension_uuid)
                    ->where('domain_uuid', $extension->domain_uuid)
                    ->whereIn('user_uuid', $removeUsers)
                    ->delete();
            }

            if (count($addUsers) > 0) {
                $query = $this->database->table($extension->users()->getTable());
                $query
                    ->insert(array_map(function ($userId) use ($extension) {
                        return [
                            'extension_user_uuid' => Str::uuid()->toString(),
                            'domain_uuid' => $extension->domain_uuid,
                            'extension_uuid' => $extension->extension_uuid,
                            'user_uuid' => $userId
                        ];
                    }, array_keys($addUsers)));
            }
        } catch (\Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
