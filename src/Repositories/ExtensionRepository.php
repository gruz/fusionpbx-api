<?php

namespace Gruz\FPBX\Repositories;

use Gruz\FPBX\Repositories\DomainRepository;
use Illuminate\Support\Str;
use Gruz\FPBX\Models\Extension;
use Gruz\FPBX\Repositories\AbstractRepository;

class ExtensionRepository extends AbstractRepository
{
    public function getNewExtension($domain_uuid)
    {
        $extensions = $this->model->where([
            ['domain_uuid', $domain_uuid],
        ])
            ->selectRaw('"extension"::bigint')
            ->whereRaw('"extension"::bigint >=' . config('fpbx.extension.min'))
            ->whereRaw('"extension"::bigint <=' . config('fpbx.extension.max'))
            ->get()
            ->pluck('extension')
            ->sort()
            ->toArray();

        do {
            $extension = rand(config('fpbx.extension.min'),config('fpbx.extension.max'));
        } while( in_array( $extension, $extensions ) );

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

    public function create(array $data, $options = [])
    {
        if (empty($data['user_context'])) {
            /**
             * @var DomainRepository
             */
            $domainRepository = app(DomainRepository::class);
            $domain = $domainRepository->getWhere('domain_uuid', $data['domain_uuid'])->first();
            // getById($data['domain_uuid'])->first();
            $data['user_context'] = $domain->getAttribute('domain_name');
        }

        return parent::create($data, $options);
    }

}
