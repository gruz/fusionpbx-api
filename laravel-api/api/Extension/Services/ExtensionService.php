<?php

namespace Api\Extension\Services;

use Api\Extension\Repositories\ExtensionRepository;
use Infrastructure\Database\Eloquent\AbstractService;

class ExtensionService extends AbstractService
{
    /**
     * @var ExtensionRepository
     */
    public $repository;

    public function getMaxExtension($domain_uuid) {
        return $this->repository->getMaxExtension($domain_uuid);
    }
}
