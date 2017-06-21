<?php

namespace Api\Status\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

use Api\Status\Events\StatusWasCreated;

use Api\Status\Repositories\StatusRepository;

use Carbon\Carbon;

class StatusService
{
    private $auth;

    private $database;

    private $dispatcher;

    private $statusRepository;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        StatusRepository $statusRepository
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->statusRepository = $statusRepository;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $data['user_uuid'] = $this->auth->user()->user_uuid;

            $dataObject = $this->statusRepository->getWhereArray($data)->first();


            if (empty($dataObject))
            {
              $dataObject = $this->statusRepository->create($data);

              $this->dispatcher->fire(new StatusWasCreated($dataObject));
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

    public function set($data)
    {
      $this->database->beginTransaction();

      try {

        $data['user_uuid'] = $this->auth->user()->user_uuid;
        $data['domain_uuid'] = $this->auth->user()->domain_uuid;

        sort($data['services']);

        $data['services'] = json_encode($data['services']);

        $dataObject = $this->statusRepository->getWhereArray($data);

        if (empty($dataObject->first()))
        {
            $this->statusRepository->create($data);
        }
        else
        {
          $dataObject->first->touch();
            // ~ $this->statusRepository->update($data);
        }

        $deadTime = Carbon::now()->subSeconds(config('api.status_lifetime'));

        $Status = $this->statusRepository->getModel();
        $Status->where('updated_at', '<', $deadTime)->delete();

        $Status = $this->statusRepository->getModel();
        $available_statuses = $Status
              ->where('domain_uuid', $data['domain_uuid'])
              ->where('updated_at', '>=', $deadTime)
                ->where('user_uuid', '<>', $data['user_uuid'])
                ->get();

      } catch (Exception $e) {
        $this->database->rollBack();
        throw $e;
      }

      $this->database->commit();

      $return = [];

      foreach ($available_statuses as $k => $status)
      {
        if (!isset($return[$status->user_uuid]))
        {
          $return[$status->user_uuid] = [];
        }

        $services = json_decode($status->services);

        if (!isset($return[$status->user_uuid]['services']))
        {
          $return[$status->user_uuid]['services'] = $services;
        }
        else
        {
          $return[$status->user_uuid]['services'] = array_unique(array_merge($return[$status->user_uuid]['services'], $services), SORT_REGULAR);
        }

        if (!isset($return[$status->user_uuid]['status']))
        {
          $return[$status->user_uuid]['status'] = $status->status;
        }
        else
        {
          $conf_statuses = config('api.statuses');
          if ($conf_statuses[$return[$status->user_uuid]['status']] <= $conf_statuses[$status->status])
          {
            // Leave old status
          }
          else
          {
            $return[$status->user_uuid]['status'] = $status->status;
          }

        }

      }


      return $return;

    }
}
