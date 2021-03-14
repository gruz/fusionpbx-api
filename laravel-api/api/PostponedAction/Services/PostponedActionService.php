<?php

namespace Api\PostponedAction\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Api\User\Services\TeamService;
use Api\PostponedAction\Models\PostponedAction;
use Api\PostponedAction\Events\PostponedActionWasCreated;

/**
 * Store request to a table to execute later on another action like mail confirmation
 *
 * @package Infrastructure\Services
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
        $hash = Str::uuid();

        $model = new PostponedAction;
        $model->request = $data;
        $model->hash = $hash;
        $model->save();

        $users = Arr::get($data, 'users', []);
        $admins = collect($users)->where('is_admin', true);

        event(new PostponedActionWasCreated($admins, $model));

        return $model;
    }

    public function executeByHash($hash)
    {
        $model = PostponedAction::where('hash', $hash)->first();

        $data = $model->request;

        return $this->teamService->create($data);

        // return $this->response($this->teamService->createDeperacted($data), 201);
        // dd($data);
        // $data = $request->all();
        // PostponedActionWasCreated::dispatch($request);


        // // php artisan make:notification InvoicePaid
        // Notification::send($users, new InvoicePaid($invoice));


        // foreach ($users2Notify as $user) {
        //     $this->notify($user);
        // }
        // return $model;
    }
}
