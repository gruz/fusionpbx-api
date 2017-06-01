<?php

namespace App\Auth\Controllers;

use Illuminate\Http\Request;
use App\Auth\LoginProxy;
use App\Auth\Requests\LoginRequest;
use App\Http\Controller;

class LoginController extends Controller
{
    private $loginProxy;

    public function __construct(LoginProxy $loginProxy)
    {
        $this->loginProxy = $loginProxy;
    }

    public function login(LoginRequest $request)
    {
        $username = $request->get('username');
        $domain_name = $request->get('domain_name');
        $password = $request->get('password');

        return $this->response($this->loginProxy->attemptLogin($username, $password, $domain_name));
    }

    public function refresh(Request $request)
    {
        return $this->response($this->loginProxy->attemptRefresh());
    }

    public function logout()
    {
        $this->loginProxy->logout();

        return $this->response(null, 204);
    }
}