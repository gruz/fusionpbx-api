<?php

namespace Gruz\FPBX\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Gruz\FPBX\Models\PostponedAction;
use Gruz\FPBX\Services\Fpbx\TeamService;
use Gruz\FPBX\Services\Fpbx\AbstractService;
use Gruz\FPBX\Events\PostponedActionWasCreated;

/**
 * Store request to a table to execute later on another action like mail confirmation
 *
 * @package Gruz\FPBX\Services
 */
class PostponedActionService extends AbstractService
{
    // private $teamService;

    // public function __construct(TeamService $teamService)
    // {
    //     $this->teamService = $teamService;
    // }

    public function createMany($data, $options = [])
    {
        // $hash = Str::uuid()->toString();
        $users = Arr::get($data, 'users', []);
        $admins = collect($users)->where('is_admin', true);

        $i = -1;
        while ($i < 5) { // Be sure we save unique codes
            $i++;
            $data2save = [];
            foreach ($admins as $admin) {
                $code = mt_rand(100000,999999);
                $row = [
                    'code' => $code,
                    'request' => [
                        'user_email' => $admin['user_email'],
                        'request' => $data,
                    ],

                ];
                $data2save[] = $row;
            }

            try {
                $options['dispatchDefaultEvent'] = false;
                $items = parent::createMany($data2save, $options);
                event(new PostponedActionWasCreated($items));
                return [
                    'message' => __('Domain creation requested. Please check your email to continue')
                ];
            } catch (\Throwable $th) {
                $ecode = $th->getCode();
                if ($ecode === '23505' && $i < 5) {
                    continue;
                }
                throw $th;
            }
        }
    }

    public function executeByHash($code)
    {
        $model = PostponedAction::where('code', $code)->first();

        $data = $model->request['request'];
        $user_email = $model->request['user_email'];

        /**
         * @var TeamService
         */
        $teamService = app(TeamService::class);
        $domainModel = $teamService->create($data, $user_email);

        if (!empty($domainModel)) {
            PostponedAction::where('request->request->domain_name', $domainModel->getAttribute('domain_name'))->delete();
        }

        return $domainModel;
    }
}
