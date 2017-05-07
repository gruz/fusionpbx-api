<?php

namespace Api\Domains\Controllers;

use Illuminate\Http\Request;
use Infrastructure\Http\Controller;
use Api\Domains\Requests\CreateUserRequest;
use Api\Domains\Requests\UserRolesRequest;
use Api\Domains\Services\UserService;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

// REMOVE JUST FOR TESTS
    public function index()
    {
        $resourceOptions = $this->parseResourceOptions();
        $data = $this->userService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }

// REMOVE JUST FOR TESTS
    public function show($userId)
    {
        $resourceOptions = $this->parseResourceOptions();
//$userId = '"' . $userId . '"';
        $data = $this->userService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'user');

        return $this->response($parsedData);
    }


    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }

    public function getById($userId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'user');

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

    public function addRoles($userId, UserRolesRequest $request)
    {
        $roles = $request->get('roles', []);

        return $this->response($this->userService->addRoles($userId, $roles));
    }

    public function setRoles($userId, UserRolesRequest $request)
    {
        $roles = $request->get('roles', []);

        return $this->response($this->userService->setRoles($userId, $roles));
    }

    public function removeRoles($userId, UserRolesRequest $request)
    {
        $roles = $request->get('roles', []);

        return $this->response($this->userService->removeRoles($userId, $roles));
    }
}
