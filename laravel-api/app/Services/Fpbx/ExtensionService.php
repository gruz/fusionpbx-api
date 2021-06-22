<?php

namespace App\Services\Fpbx;

use App\Repositories\ExtensionRepository;

class ExtensionService extends AbstractService
{
    /**
     * @var ExtensionRepository
     */
    public $repository;

    public function getNewExtension($domain_uuid) {
        return $this->repository->getNewExtension($domain_uuid);
    }
}
