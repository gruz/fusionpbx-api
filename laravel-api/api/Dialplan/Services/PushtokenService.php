<?php

namespace Api\Dialplan\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

use Api\Dialplan\Events\PushtokenWasCreated;

use Api\Dialplan\Repositories\PushtokenRepository;

class PushtokenService
{
    private $auth;

    private $database;

    private $dispatcher;

    private $pushtokenRepository;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        PushtokenRepository $pushtokenRepository
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->pushtokenRepository = $pushtokenRepository;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $data['user_uuid'] = $this->auth->user()->user_uuid;

            $dataObject = $this->pushtokenRepository->getWhereArray($data)->first();


            if (empty($dataObject))
            {
              $dataObject = $this->pushtokenRepository->create($data);

              $this->dispatcher->fire(new PushtokenWasCreated($dataObject));
            }
            else
            {
              $dataObject['message'] = __('Already exists. Nothing to be done');
            }

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $dataObject;
    }
}
