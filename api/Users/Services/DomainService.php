<?php

namespace Api\Users\Services;

use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Api\Users\Exceptions\DomainNotFoundException;
use Api\Users\Events\DomainWasCreated;
use Api\Users\Events\DomainWasDeleted;
use Api\Users\Events\DomainWasUpdated;
use Api\Users\Repositories\DomainRepository;

class DomainService
{
    private $database;

    private $dispatcher;

    public function __construct(
        DatabaseManager $database,
        Dispatcher $dispatcher,
        DomainRepository $domainRepository
    ) {
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->domainRepository = $domainRepository;
    }

    public function getAll($options = [])
    {
        return $this->domainRepository->get($options);
    }

    public function getById($domainId, array $options = [])
    {
        $domain = $this->getRequestedDomain($domainId);

        return $domain;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $domain = $this->domainRepository->create($data);

            $this->dispatcher->fire(new DomainWasCreated($domain));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $domain;
    }

    public function update($domainId, array $data)
    {
        $domain = $this->getRequestedDomain($domainId);

        $this->database->beginTransaction();

        try {
            $this->domainRepository->update($domain, $data);

            $this->dispatcher->fire(new DomainWasUpdated($domain));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $domain;
    }

    public function delete($domainId)
    {
        $domain = $this->getRequestedDomain($domainId);

        $this->database->beginTransaction();

        try {
            $this->domainRepository->delete($domainId);

            $this->dispatcher->fire(new DomainWasDeleted($domain));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

    private function getRequestedDomain($domainId)
    {
        $domain = $this->domainRepository->getById($domainId);

        if (is_null($domain)) {
            throw new DomainNotFoundException();
        }

        return $domain;
    }
}
