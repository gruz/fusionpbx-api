<?php

namespace Infrastructure\Traits;

use Illuminate\Database\Eloquent\Collection;


trait OneToManyRelationCRUD
{
  // ~ $this->setGroups($user->user_uuid, [$data['group_uuid']]);
  public function __call($name, $arguments)
  {
    static $allowedMethods = ['add', 'checkValidityOf', 'getRequested', 'map', 'remove', 'set'];

    $callToAllowedMethod = false;

    foreach ($allowedMethods as $k => $methodStart) {
      if (strpos($name, $methodStart) === 0) {
        $callToAllowedMethod = true;
        break;
      }
    }

    if (!$callToAllowedMethod) {
      throw new \Exception("no magic left");
    }

    $scope  = explode($methodStart, $name, 2);
    $scope = $scope[1];

    switch ($methodStart) {
      case 'add':
      case 'set':
      case 'remove':
      case 'checkValidityOf':
        $methodToCall = $methodStart . 'OneToManyRelations';
        break;
      case 'getRequested':
        $methodToCall = $methodStart . 'Object';
        $scope = null;
        break;
      case 'map':
        $methodToCall = $methodStart . 'NamesToIds';
        $scope = explode('NamesTo', $scope, 2);
        $scope = $scope[0];
        break;
      default:

        break;
    }

    if (method_exists($this, $methodToCall)) {
      if (!empty($scope)) {
        array_unshift($arguments, $scope);
      }
      return call_user_func_array(array($this, $methodToCall), $arguments);
    }

    throw new \Exception("no magic left");
  }

  public function addOneToManyRelations($scope, $oneId, array $manyIds)
  {
    $relation = strtolower($scope);
    $relation_singular = substr($relation, 0, -1);

    // ~ $user = $this->getRequestedUser($userId, [
    // ~ 'includes' => ['groups']
    // ~ ]);

    $one = $this->getRequestedObject($oneId, [
      'includes' => [$relation]
    ]);

    // ~ $currentGroups = $user->groups->pluck('id')->toArray();
    $currentManys = $one->$relation->pluck($relation_singular . '_uuid')->toArray();

    // ~ $groups = $this->checkValidityOfGroups($groupIds);
    $manys = $this->checkValidityOfOneToManyRelations($scope, $manyIds);

    // ~ $this->userRepository->setGroups($user, $groupIds);
    $this->{$this->scope . 'Repository'}->{'set' . $scope}($one, $manyIds);

    // ~ $groups
    // ~ ->filter(function ($group) use ($currentGroups) {
    // ~ return !in_array($group->id, $currentGroups);
    // ~ })
    // ~ ->each(function ($group) use ($user) {
    // ~ $user->groups->add($group);
    // ~ });
    $manys
      ->filter(function ($many) use ($currentManys) {
        return !in_array($many->id, $currentManys);
      })
      ->each(function ($many) use ($one, $relation) {
        $one->{$relation}->add($many);
      });

    return $one;
  }

  // ~ public function setOneToManyRelations($userId, array $groupIds)
  public function setOneToManyRelations($scope, $oneId, array $manyIds)
  {

    $relation = strtolower($scope);
    $relation_singular = substr($relation, 0, -1);

    $one = $this->getRequestedObject($oneId, [
      'includes' => [$relation]
    ]);


    $currentManys = $one->{$relation}->pluck($relation_singular . '_uuid')->toArray();
    $manys = $this->checkValidityOfOneToManyRelations($scope, $manyIds);

    $remove = array_diff($currentManys, $manyIds);
    $add = array_diff($manyIds, $currentManys);

    $remove = $this->mapNamesToIds($scope, $remove);
    $add = $this->mapNamesToIds($scope, $add);


    // ~ $this->userRepository->setGroups($user, $add, $remove);
    $this->{$this->scope . 'Repository'}->{'set' . $scope}($one, $add, $remove);

    $one->setRelation($relation, new Collection($manys));

    return $one;
  }

  public function removeOneToManyRelations($scope, $oneId, array $manyIds)
  {
    $relation = strtolower($scope);
    $relation_singular = substr($relation, 0, -1);

    $one = $this->getRequestedObject($oneId, [
      'includes' => [$relation]
    ]);

    $manys = $this->checkValidityOfOneToManyRelations($scope, $manyIds);

    // ~ $this->userRepository->setGroups($user, [], $groupIds);
    $this->{$this->scope . 'Repository'}->{'set' . $scope}($one, [], $manyIds);

    $updatedManyCollection = $one->{$relation}->filter(function ($many) use ($manyIds) {
      return !in_array($many->id, $manyIds);
    });

    // ~ $user->setRelation('groups', $updatedGroupCollection);
    $one->setRelation($relation, $updatedManyCollection);

    return $one;
  }

  private function checkValidityOfOneToManyRelations($scope, array $manyIds = [])
  {
    $relation = strtolower($scope);
    $relation_singular = substr($relation, 0, -1);

    // ~ $groups = $this->groupRepository->getWhereIn('group_uuid', $groupIds);
    $manys = $this->{$relation_singular . 'Repository'}->getWhereIn($relation_singular . '_uuid', $manyIds);

    if (count($manyIds) !== $manys->count()) {
      $missing = array_diff($manyIds, $manys->pluck($relation_singular . '_uuid')->toArray());
      $exceptionName = 'Api\\' . ucfirst($this->scope) . 's\\Exceptions\\Invalid' . ucfirst($relation_singular) . 'Exception';

      throw new $exceptionName([$relation_singular . 'Id' => $missing[0]]);
    }

    return $manys;
  }

  public function getRequestedObject($Id, array $options = [])
  {
    $object = $this->{$this->scope . 'Repository'}->getById($Id, $options);

    if (is_null($object)) {
      $exceptionName = 'Api\\' . ucfirst($this->scope) . 's\\Exceptions\\' . ucfirst($scope) . 'NotFoundException';
      throw new  $exceptionName();
    }

    return $object;
  }

  public function mapNamesToIds($scope, array $manyIds, Collection $manys = null)
  {
    /*
      if (empty($groups))
      {
        $groups = $this->checkValidityOfGroups($groupIds);
      }

      $return = [];

      foreach ($groupIds as $k => $v)
      {
        $return[$v] = $groups->where('group_uuid', $v)->first()->group_name;

      }
      */
    $relation = strtolower($scope);
    $relation_singular = substr($relation, 0, -1);

    if (empty($manys)) {
      $manys = $this->checkValidityOfOneToManyRelations($scope, $manyIds);
    }
    $return = [];

    $suffix = '_name';

    if ($scope == 'Users') {
      $suffix = 'name';
    }

    foreach ($manyIds as $k => $v) {
      $return[$v] = $manys->where($relation_singular . '_uuid', $v)->first()->{$relation_singular . $suffix};
    }

    return $return;
  }

  private function setScope()
  {
    $currentService = explode('\\', static::class);
    $scope = end($currentService);
    $scope = explode('Service', $scope);
    $scope = $scope[0];
    $this->scope = strtolower($scope);
  }
}

// Here are original functions
class FakeTempClassClosuremygruz20170524044452
{

  public function addGroups($userId, array $groupIds)
  {
    $user = $this->getRequestedUser($userId, [
      'includes' => ['groups']
    ]);

    $currentGroups = $user->groups->pluck('id')->toArray();
    $groups = $this->checkValidityOfGroups($groupIds);

    $this->userRepository->setGroups($user, $groupIds);

    $groups
      ->filter(function ($group) use ($currentGroups) {
        return !in_array($group->id, $currentGroups);
      })
      ->each(function ($group) use ($user) {
        $user->groups->add($group);
      });

    return $user;
  }

  public function setGroups($userId, array $groupIds)
  {
    $user = $this->getRequestedUser($userId, [
      'includes' => ['groups']
    ]);

    $currentGroups = $user->groups->pluck('user_uuid')->toArray();
    $groups = $this->checkValidityOfGroups($groupIds);

    $remove = array_diff($currentGroups, $groupIds);
    $add = array_diff($groupIds, $currentGroups);

    $remove = $this->mapGroupNamesToGroupIds($remove);
    $add = $this->mapGroupNamesToGroupIds($add);

    $this->userRepository->setGroups($user, $add, $remove);

    $user->setRelation('groups', new Collection($groups));

    return $user;
  }

  public function removeGroups($userId, array $groupIds)
  {
    $user = $this->getRequestedUser($userId, [
      'includes' => ['groups']
    ]);

    $groups = $this->checkValidityOfGroups($groupIds);

    $this->userRepository->setGroups($user, [], $groupIds);

    $updatedGroupCollection = $user->groups->filter(function ($group) use ($groupIds) {
      return !in_array($group->id, $groupIds);
    });
    $user->setRelation('groups', $updatedGroupCollection);

    return $user;
  }

  private function checkValidityOfGroups(array $groupIds = [])
  {
    $groups = $this->groupRepository->getWhereIn('group_uuid', $groupIds);

    if (count($groupIds) !== $groups->count()) {
      $missing = array_diff($groupIds, $groups->pluck('group_uuid')->toArray());
      throw new InvalidGroupException(['groupId' => $missing[0]]);
    }

    return $groups;
  }

  private function getRequestedUser($userId, array $options = [])
  {
    $user = $this->userRepository->getById($userId, $options);

    if (is_null($user)) {
      throw new UserNotFoundException();
    }

    return $user;
  }

  public function mapGroupNamesToGroupIds(array $groupIds, Collection $groups = null)
  {
    if (empty($groups)) {
      $groups = $this->checkValidityOfGroups($groupIds);
    }

    $return = [];

    foreach ($groupIds as $k => $v) {
      $return[$v] = $groups->where('group_uuid', $v)->first()->group_name;
    }

    return $return;
  }
}
