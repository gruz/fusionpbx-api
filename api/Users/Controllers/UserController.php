<?php

namespace Api\Users\Controllers;

use Illuminate\Http\Request;
use Infrastructure\Http\Controller;
use Api\Users\Requests\CreateUserRequest;
use Api\Users\Requests\SignupRequest;
use Api\Users\Requests\UserGroupsRequest;
use Api\Users\Services\UserService;
use Api\Users\Services\TeamService;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService, TeamService $teamService)
    {
        $this->userService = $userService;
        $this->teamService = $teamService;
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
        $parsedData = $this->parseData($data, $resourceOptions, 'user');

        return $this->response($parsedData);
    }

    public function getMe()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getMe($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }


    public function create(CreateUserRequest $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }

    public function update($userId, Request $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->update($userId, $data));
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
		public function signup(SignupRequest $request)
    {
        $data = $request->get('team', []);

        if (empty($data))
        {
          $data = $request->get('user', []);
          $data['isTeam'] = false;
          $data['group_name'] = env('DEFAULT_USER_GROUP_NAME');
          $data['user_enabled'] = 'false';

          return $this->response($this->userService->create($data, false), 201);
        }

        $data['isTeam'] = true;
        $data['user_enabled'] = 'true';
        $data['group_name'] = env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME');

        return $this->response($this->teamService->create($data), 201);
    }
}
