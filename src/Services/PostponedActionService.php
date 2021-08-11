<?php

namespace Gruz\FPBX\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Gruz\FPBX\Services\Fpbx\TeamService;
use Gruz\FPBX\Models\PostponedAction;
use Gruz\FPBX\Events\PostponedActionWasCreated;

/**
 * Store request to a table to execute later on another action like mail confirmation
 *
 * @package Gruz\FPBX\Services
 */
class PostponedActionService
{
    private $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function create(array $data)
    {
        $hash = Str::uuid()->toString();

        $model = new PostponedAction;
        $model->request = $data;
        $model->hash = $hash;
        $model->save();

        $users = Arr::get($data, 'users', []);
        $admins = collect($users)->where('is_admin', true);

        event(new PostponedActionWasCreated($admins, $model));

        return $model;
    }

    public function executeByHash($hash, $email)
    {
        $model = PostponedAction::where('hash', $hash)->first();

        $data = $model->request;

        $domainModel = $this->teamService->create($data, $email);


        if (!empty($domainModel)) {
            $model->delete();
            // PostponedAction::where('request->domain_name', $domainModel->getAttribute('domain_name'))->delete();
        }

        return $domainModel;
    }
}
