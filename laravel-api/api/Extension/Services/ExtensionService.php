<?php

namespace Api\Extension\Services;

use Api\Extension\Repositories\ExtensionRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Infrastructure\Database\Eloquent\AbstractService;

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
