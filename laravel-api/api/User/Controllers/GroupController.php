<?php

namespace Api\User\Controllers;

use Illuminate\Http\Request;
use Infrastructure\Http\Controller;
use Api\User\Requests\CreateGroupRequest;
use Api\User\Services\GroupService;

class GroupController extends Controller
{
    private $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->groupService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'groups');

        return $this->response($parsedData);
    }

    public function getById($userId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->groupService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'group');

        return $this->response($parsedData);
    }

    public function create(CreateGroupRequest $request)
    {
        $data = $request->get('group', []);

        return $this->response($this->groupService->create($data), 201);
    }

    public function update($groupId, Request $request)
    {
        $data = $request->get('group', []);

        return $this->response($this->groupService->update($userId, $data));
    }

    public function delete($groupId)
    {
        return $this->response($this->groupService->delete($groupId));
    }
}
