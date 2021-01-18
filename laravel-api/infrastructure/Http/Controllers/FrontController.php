<?php

namespace Infrastructure\Http\Controllers;

use Api\User\Models\User;
use Illuminate\Routing\Controller as BaseController;

class FrontController extends BaseController
{
    public function test()
    {
        if (!config('app.debug')) {
            return;
        }
        \DB::enableQueryLog(); // Enable query log
        $items = \Api\Extension\Models\Extension::with('extension_users.permissions')
            ->where('extension_uuid', '63045580-4ce3-11eb-b21b-47744eb6524b')
            ->get()
            ->toArray();
        \dd($items, \DB::getQueryLog());

        phpinfo();
        try {

            \DB::enableQueryLog(); // Enable query log
            $user = User::with('groups')
                ->get()->toArray();

            \dd($user, \DB::getQueryLog());
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
