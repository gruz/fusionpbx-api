<?php

namespace App\Http\Controllers;

use Api\User\Models\User;
use Illuminate\Routing\Controller as BaseController;

class FrontController extends BaseController
{

    public function test() {
        phpinfo();
        try {

            \DB::enableQueryLog(); // Enable query log
            $user = User::
            with('groups')
            ->get()->toArray();
    
            \dd($user,\DB::getQueryLog());

        } catch (\Exception $e) {
            dd($e);
            
        }
    }
}