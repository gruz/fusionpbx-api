<?php

namespace Api\User\Controllers;

use Illuminate\Http\Request;
use Api\User\Services\UserService;
use Api\User\Services\TeamService;
use Infrastructure\Http\Controller;
use Api\User\Requests\SignupRequest;
use Api\User\Services\UserPasswordService;
use Api\User\Requests\CreateUserRequest;
use Api\User\Requests\UserGroupsRequest;
use Api\User\Requests\UserResetPasswordRequest;
use Api\User\Requests\UserForgotPasswordRequest;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var TeamService
     */
    private $teamService;

    /**
    * @var UserPasswordService
    */
    private $passwordService;

    public function __construct(UserService $userService,
                                TeamService $teamService,
                                UserPasswordService $passwordService)
    {
        $this->userService = $userService;
        $this->teamService = $teamService;
        $this->passwordService = $passwordService;
    }

    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }

    public function getById(string $userId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }

    public function getMe()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getMe($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }


    public function create(CreateUserRequest $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }

    public function update($hash, Request $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->update($userId, $data));
    }

    public function activate(string $hash)
    {
        $response = $this->response($this->userService->activate($hash));
        // dd($response);
        return $response;
    }

    public function delete($userId)
    {
        return $this->response($this->userService->delete($userId));
    }

    public function addGroups($userId, UserGroupsRequest $request)
    {
        $groups = $request->get('groups', []);

        return $this->response($this->userService->addGroups($userId, $groups));
    }

    public function setGroups($userId, UserGroupsRequest $request)
    {
        $groups = $request->get('groups', []);

        return $this->response($this->userService->setGroups($userId, $groups));
    }

    public function removeGroups($userId, UserGroupsRequest $request)
    {
        $groups = $request->get('groups', []);

        return $this->response($this->userService->removeGroups($userId, $groups));
    }

    // ~ public function create(CreateUserRequest $request)
    // ~ {
    // ~ $data = $request->get('user', []);

    // ~ return $this->response($this->userService->create($data), 201);
    // ~ }

    /**
     * User signup
     *
     * @param SignupRequest $request
     * @return void
     */
    public function signup(SignupRequest $request)
    {
        $data = $request->get('team', []);

        if (empty($data)) {
            $data = $request->get('user', []);

            $data['isTeam'] = false;
            $data['group_name'] = env('DEFAULT_USER_GROUP_NAME');

            // Since there is no a field dedicated to activation, Gruz have decided to use the quazi-boolean user_enabled field.
            // FusionPBX recognizes non 'true' as FALSE. So our hash in the user_enabled field is treated as FALSE till user is activated.
            $data['user_enabled'] = md5(uniqid() . microtime());

            return $this->response($this->userService->create($data, false), 201);
        }

        $data['isTeam'] = true;
        $data['user_enabled'] = 'true';
        $data['group_name'] = env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME');

        return $this->response($this->teamService->createDeperacted($data), 201);
    }

    /**
     * User forgot password 
     *
     * @param UserForgotPasswordRequest $request
     * @return void
     */
    public function forgotPassword(UserForgotPasswordRequest $request) 
    {
        $email = $request->get('user_email');   
        $domain_name = $request->get('domain_name');

        return $this->response($this->passwordService->generateResetToken($email, $domain_name));
    }

    /**
     * User reset password 
     *
     * @param UserResetPasswordRequest $request
     * @return void
     */
    public function resetPassword(UserResetPasswordRequest $request) 
    {
        $email = $request->get('user_email');   

        return $this->response($this->passwordService->resetPassword($email));
    }
}
