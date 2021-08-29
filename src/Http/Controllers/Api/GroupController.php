<?php

namespace Gruz\FPBX\Http\Controllers\Api;

use Illuminate\Http\Request;
use Gruz\FPBX\Services\Fpbx\GroupService;
use Gruz\FPBX\Requests\CreateGroupRequest;

class GroupController extends AbstractBrunoController
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
