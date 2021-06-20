<?php

namespace App\Services;

use App\Repositories\ExtensionRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use App\Database\Eloquent\AbstractService;

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
