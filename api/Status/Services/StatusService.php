<?php

namespace Api\Status\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

use Api\Status\Repositories\StatusRepository;

use Api\Status\Exceptions\StatusNotFoundException;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class StatusService
{
    // Must be public because is used in Ratchet to reinit Auth
    // See ./app/RatchetServer.php function initStatus
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
            $data['user_uuid'] = Auth::user()->user_uuid;

            $dataObject = $this->statusRepository->getWhereArray($data)->first();


            if (empty($dataObject))
            {
              $dataObject = $this->statusRepository->create($data);

              // ~ $this->dispatcher->fire(new StatusWasCreated($dataObject));
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

    public function update($uuid, array $data)
    {
        $object = $this->findUserStatus($uuid);

        if (is_null($object)) {
            throw new StatusNotFoundException();
        }

        $this->database->beginTransaction();

        try {
            $this->statusRepository->update($object, $data);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $object;
    }

    public function setStatus($data)
    {
      $this->database->beginTransaction();

      try {

        $data['user_uuid'] = Auth::user()->user_uuid;
        $data['domain_uuid'] = Auth::user()->domain_uuid;

        if (isset($data['services']))
        {
          sort($data['services']);

          $data['services'] = json_encode($data['services']);
        }

        $dataObject = $this->statusRepository->getWhereArray(['user_uuid' => $data['user_uuid'], 'domain_uuid' => $data['domain_uuid']])->first();

        if (empty($dataObject))
        {
          $return = $this->statusRepository->create($data);
        }
        else
        {
          $dataObject->touch();
          $return = $this->statusRepository->update($dataObject, $data);
        }

        // Remove all outdated statuses
        $deadTime = Carbon::now()->subSeconds(config('api.status_lifetime'));
        $Status = $this->statusRepository->getModel();
        $Status->where('updated_at', '<', $deadTime)->delete();
        /*
        $Status = $this->statusRepository->getModel();
        $available_statuses = $Status
              ->where('domain_uuid', $data['domain_uuid'])
              ->where('updated_at', '>=', $deadTime)
                // ~ ->where('user_uuid', '<>', $data['user_uuid'])
                ->get();
        */

      } catch (Exception $e) {
        $this->database->rollBack();
        throw $e;
      }

      $this->database->commit();

      return ['users' => $return];

      $return = [];


      foreach ($available_statuses as $k => $status)
      {
        $ret = [];
        /*
        if (!isset($return[$status->user_uuid]))
        {
          $return[$status->user_uuid] = [];
        }
        */

        $services = json_decode($status->services);

        if (!isset($return[$status->user_uuid]['services']))
        {
          $return[$status->user_uuid]['services'] = $services;
        }
        else
        {
          $return[$status->user_uuid]['services'] = array_unique(array_merge($return[$status->user_uuid]['services'], $services), SORT_REGULAR);
        }

        if (!isset($return[$status->user_uuid]['user_status']))
        {
          $return[$status->user_uuid]['user_status'] = $status->user_status;
        }
        else
        {
          $conf_statuses = config('api.statuses');
          if ($conf_statuses[$return[$status->user_uuid]['user_status']] <= $conf_statuses[$status->user_status])
          {
            // Leave old status
          }
          else
          {
            $return[$status->user_uuid]['user_status'] = $status->user_status;
          }

        }

      }

      return $return;
    }

    public function findUserStatus($user_uuid)
    {
      return $this->statusRepository->setOrdering('updated_at', 'DESC')->getWhere('user_uuid', $user_uuid)->first();
    }
}
