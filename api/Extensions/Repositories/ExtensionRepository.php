<?php

namespace Api\Extensions\Repositories;

use Api\Extensions\Models\Extension;
use App\Database\Eloquent\Repository;

class ExtensionRepository extends Repository
{
    public function getModel()
    {
        return new Extension();
    }

    public function create(array $data)
    {
        $extension = $this->getModel();

        $extension->fill($data);
        $extension->save();

        return $extension;
    }

    public function update(Extension $extension, array $data)
    {
        $extension->fill($data);

        $extension->save();

        return $extension;
    }

    public function setUsers(Extension $extension, array $addUsers, array $removeUsers = [])
    {
        $this->database->beginTransaction();

        try {
            if (count($removeUsers) > 0) {
                $query = $this->database->table($extension->extension_users()->getTable());
                $query
                    ->where('extension_uud', $extension->extension_uuid)
                    ->where('domain_uuid', $extension->domain_uuid)
                    ->whereIn('user_uuid', $removeUsers)
                    ->delete();
            }

            if (count($addUsers) > 0) {
                $query = $this->database->table($extension->extension_users()->getTable());
                $query
                    ->insert(array_map(function ($userId) use ($extension) {
                        return [
                            'extension_user_uuid' => \Uuid::generate(),
                            'domain_uuid' => $extension->domain_uuid,
                            'extension_uuid' => $extension->extension_uuid,
                            'user_uuid' => $userId
                        ];
                    }, array_keys($addUsers)));
            }
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

}
