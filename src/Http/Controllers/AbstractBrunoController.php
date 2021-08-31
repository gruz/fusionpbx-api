<?php

namespace Gruz\FPBX\Http\Controllers;

use Optimus\Bruno\LaravelController;
use Gruz\FPBX\Requests\GetUuidRequest;

abstract class AbstractBrunoController extends LaravelController
{

    protected function getService()
    {
        $className = $this->getBaseClassName('Services', 'Service');

        if (class_exists($className)) {
            $object =  app($className);
            return $object;
        }

        return null;
    }

    private function getBaseClassName($replace, $suffix)
    {
        preg_match('/.*\\\\(.*)Controller$/', get_class($this), $matches);
        $entity = $matches[1];
        $className = 'Gruz\FPBX\\' . $replace . '\\FPBX\\' . $entity . $suffix;

        return $className;
    }

    private function getEntityName()
    {
        preg_match('/.*\\\\(.*)Controller$/', get_class($this), $matches);
        $entity = $matches[1];
        $entity = strtolower($entity) . 's';
        return $entity;
    }

    public function getById(string $uuid, GetUuidRequest $request)
    {
        $resourceOptions = $this->parseResourceOptions();

        $entiryService = $this->getService();

        $data = $entiryService->getById($uuid, $resourceOptions);

        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }

    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $entiryService = $this->getService();
        $entityName = $this->getEntityName();

        $data = $entiryService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, $entityName);

        return $this->response($parsedData);
    }
}
