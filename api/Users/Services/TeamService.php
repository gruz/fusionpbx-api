<?php

namespace Api\Users\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

use Api\Users\Services\UserService;
use Api\Users\Services\DomainService;
use App\Services\FreeSwicthSocketService as FSSocketService;

use \Api\Users\Exceptions\InvalidGroupException;
use \Api\Users\Exceptions\UserNotFoundException;
use \Api\Users\Exceptions\DomainExistsException;


use \Api\Users\Events\UserWasCreated;
use \Api\Users\Events\UserWasDeleted;
use \Api\Users\Events\UserWasUpdated;
use \Api\Users\Events\DomainWasCreated;


use \Api\Users\Repositories\GroupRepository;
use \Api\Users\Repositories\UserRepository;
use \Api\Users\Repositories\DomainRepository;
use \Api\Users\Repositories\ContactRepository;
use \Api\Users\Repositories\Contact_emailRepository;

use Api\Settings\Models\Default_setting;

class TeamService
{
    private $auth;

    private $database;

    private $dispatcher;

    private $groupRepository;

    private $userRepository;

    private $domainService;

    private $domainRepository;

    private $contactRepository;

    private $contact_emailRepository;

    private $userService;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        DomainService $domainService,
        DomainRepository $domainRepository,
        ContactRepository $contactRepository,
        Contact_emailRepository $contact_emailRepository,
        UserService $userService
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->domainService = $domainService;
        $this->domainRepository = $domainRepository;
        $this->contactRepository = $contactRepository;
        $this->contact_emailRepository = $contact_emailRepository;
        $this->userService = $userService;
    }

    public function getAll($options = [])
    {
        return $this->userRepository->get($options);
    }

    public function getById($userId, array $options = [])
    {
        $user = $this->getRequestedUser($userId);

        return $user;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {

            if ($this->domainRepository->getWhere('domain_name', $data['domain_name'])->count() > 0)
            {
              throw new DomainExistsException();
            }

            $data['domain_enabled'] =  'true';
            $data['domain_description'] =  'Created via api at ' . date( 'Y-m-d H:i:s', time() );

            $domain = $this->domainService->create($data);

            $data['domain_uuid'] = $domain->getAttribute('domain_uuid');

            $user = $this->userService->create($data);

            // ~ $data = array_merge($data, $user);
            /*
            $data['contact_type'] = 'user';
            $data['contact_nickname'] = $data['email'];


            $contact = $this->contactRepository->create($data);
            $contact->addHidden(['domain_uuid']);
            $data['contact_uuid'] = $contact->getAttribute('contact_uuid');

            $data['email_primary'] = 1;
            $data['email_address'] = $data['email'];

            $contact_email = $this->contact_emailRepository->create($data);
            $contact_email->addHidden(['domain_uuid', 'contact_uuid']);

            // ~ $data['username'] = $data['email'];
            $data['user_enabled'] = 'true';
            // $data['add_user'] = 'admin';
            // $data['add_date'] = 'admin';

            $user = $this->userRepository->create($data);
            $user->addHidden(['domain_uuid', 'contact_uuid']);

            // Get default group for a new team
            $group = $this->groupRepository->getWhere('group_name', env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME'));
            $data['group_uuid'] = $group->first()->group_uuid;

            $this->userService->setGroups($user->user_uuid, [$data['group_uuid']]);
            //$user = $this->userService->addGroup($data);


            $contact->setRelation('contact_email', $contact_email);
            $user->setRelation('contact', $contact);
            */

            $domain->setRelation('admin_user', $user);

            $domain->message = __('messages.team created', [
                'username' => $data['username'],
                'domain_name' => $data['domain_name'],
                'password' => $data['password']
              ]);

            // This event to be created if really needed. E.g. to notify superadmins about the fact
            // ~ $this->dispatcher->fire(new TeamWasCreated($domain));

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        $this->runFusionPBX_upgrade_domains($domain);

        return $domain;
    }

    /**
     * TODO Name or short description
     *
     * Here Gruz'd call
     * require_once app('fpath') . "/core/upgrade/upgrade_domains.php";
     * But it checks permissions which we don't need when creating a team. So I copy only needed part from the file above
     *
     * @param   Api\Users\Models\Domain  $domain
     *
     * @return   void
     */
    protected function runFusionPBX_upgrade_domains($domain)
    {
      // Some code for reference below. Don't use it as is forced to use native FusionPBX code
      /* Create domain folder for recordings (see fusionpbx/app/recordings/app_defaults.php 28)
       * The code from FusionPBX looks like below. So let's rewrite it
       *
        //if the recordings directory doesn't exist then create it
        if (is_array($_SESSION['switch']['recordings']) && strlen($_SESSION['switch']['recordings']['dir']."/".$domain_name) > 0) {
          if (!is_readable($_SESSION['switch']['recordings']['dir']."/".$domain_name)) { event_socket_mkdir($_SESSION['switch']['recordings']['dir']."/".$domain_name,02770,true); }
        }

      $settings = new Default_setting;
      $dir = $settings->where([
        'default_setting_category' => 'switch',
        'default_setting_subcategory' => 'recordings',
        'default_setting_name' => 'dir',
      ])->first()->default_setting_value . '/' . $data['domain_name'];

      $socket = new FSSocketService;
      $result = $socket->mkdir($dir);
      *
      */

      $current_path = getcwd();
      chdir(config('app.fpath_full'));
      exec('php ./core/upgrade/upgrade_domains.php', $result);
      chdir($current_path);
      return;

      /*
      $output_format = 'text';
      if (!defined('PROJECT_PATH'))
      {
        define('PROJECT_PATH', config('app.fpath_project'));
      }

      include "root.php";

      $_SERVER["DOCUMENT_ROOT"] = config('app.fpath_document_root');

      require_once "resources/require.php";
      require_once "resources/check_auth.php";

      require_once "resources/classes/config.php";
      require_once "resources/classes/domains.php";
      $domain = new \domains;
      $domain->upgrade();

      //clear the domains session array to update it
      unset($_SESSION);
      */

    }

    public function update($userId, array $data)
    {
        $user = $this->getRequestedUser($userId);

        $this->database->beginTransaction();

        try {
            $this->userRepository->update($user, $data);

            $this->dispatcher->fire(new UserWasUpdated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function delete($userId)
    {
        $user = $this->getRequestedUser($userId);

        $this->database->beginTransaction();

        try {
            $this->userRepository->delete($userId);

            $this->dispatcher->fire(new UserWasDeleted($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

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

        $currentGroups = $user->groups->pluck('id')->toArray();
        $groups = $this->checkValidityOfGroups($groupIds);

        $remove = array_diff($currentGroups, $groupIds);
        $add = array_diff($groupIds, $currentGroups);

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
        $groups = $this->groupRepository->getWhereIn('id', $groupIds);

        if (count($groupIds) !== $groups->count()) {
            $missing = array_diff($groupIds, $groups->pluck('id')->toArray());
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
}
