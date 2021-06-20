<?php

namespace Api\Domain\Services;

use Exception;
use Illuminate\Support\Arr;
use Api\User\Services\UserService;
use App\Events\UserWasDeleted;
use App\Events\UserWasUpdated;
use App\Events\TeamWasCreated;
use Api\Domain\Services\DomainService;
use App\Exceptions\DomainExistsException;
use App\Database\Eloquent\AbstractService;
use Illuminate\Database\Eloquent\Collection;
use Api\Domain\Repositories\DomainRepository;
use Api\Domain\Services\DomainSettingService;
use App\Exceptions\InvalidGroupException;
use Api\Dialplan\Repositories\DialplanRepository;
use Illuminate\Contracts\Container\BindingResolutionException;

class TeamService extends AbstractService
{
    private $domainService;

    private $dialplanRepository;

    private $domainSettingService;

    private $userService;

    public function __construct(
        DomainService $domainSevice,
        DialplanRepository $dialplanRepository,
        DomainSettingService $domainSettingService,
        UserService $userService
    ) {
        $this->domainService = $domainSevice;
        $this->dialplanRepository = $dialplanRepository;
        $this->domainSettingService = $domainSettingService;
        $this->userService = $userService;
        parent::__construct();
    }

    /**
     * Updates data posted by a user with system defaults
     *
     * The method is public for testing purposes
     *
     * @param array $data
     * @return array
     * @throws BindingResolutionException
     */
    public function prepareData(array $data)
    {
        $is_subdomain = Arr::get($data, 'is_subdomain', config('fpbx.default.domain.new_is_subdomain'));

        if ($is_subdomain) {
            $data['domain_name'] = $data['domain_name'] . '.' . config('fpbx.default.domain.mothership_domain');
        }

        if (!config('fpbx.domain.enabled')) {
            $data['domain_enabled'] = false;
        } else {
            $data['domain_enabled'] = Arr::get($data, 'domain_enabled', config('fpbx.domain.enabled'));
        }

        if (config('domain_enabled_field_type') === 'text') {
            $data['domain_enabled'] = $data['domain_enabled'] ? 'true' : 'false';
        }

        $data['domain_description'] =  Arr::get($data, 'domain_description', config('fpbx.domain.description'));

        return $data;
    }

    public function create($data, $activatorEmail = null)
    {
        $data = $this->prepareData($data);

        $this->database->beginTransaction();

        try {
            /**
             * @var DomainRepository
             */
            $domainRepository = $this->domainService->getRepository();
            if ($domainRepository->getWhere('domain_name', $data['domain_name'])->count() > 0) {
                throw new DomainExistsException();
            }

            $this->dialplanRepository->createDefaultDialplanRules();
            $domainModel = $this->domainService->create($data, ['forceFillable' => ['domain_enabled']]);

            $settingsData = Arr::get($data, 'settings', []);
            $settingsData = $this->injectData($settingsData, ['domain_uuid' => $domainModel->domain_uuid]);
            $this->domainSettingService->createMany($settingsData, ['forceFillable' => ['domain_uuid']]);

            $reseller_reference_code = Arr::get($data, 'reseller_reference_code');
            $usersData = Arr::get($data, 'users', []);
            $usersData = $this->injectData($usersData, ['domain_uuid' => $domainModel->domain_uuid]);
            if (!empty($reseller_reference_code)) {
                $usersData = $this->injectData($usersData, ['reseller_reference_code' => $reseller_reference_code]);
            }

            $usersModel = $this->userService->createMany($usersData, ['excludeNotification' => [$activatorEmail]]);

            foreach ($usersModel as $userModel) {
                if ($activatorEmail === $userModel->getAttribute('user_email')) {
                    $this->userService->activate($userModel->getAttribute('user_enabled'), false);
                    break;
                }
            }

            $activatorUserData = collect($usersData)->where('user_email', $activatorEmail)->first();

            $domainModel->message = __('messages.team created', [
                'username' => $activatorUserData['username'],
                'domain_name' => $data['domain_name'],
                'password' => $activatorUserData['password']
            ]);

            $this->dispatcher->dispatch(new TeamWasCreated($domainModel, $usersModel, $activatorUserData));
            // dd('done'); 
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();


        return $domainModel;
    }
    /**
     * TODO Name or short description
     *
     * Here Gruz'd call
     * require_once app('fpath') . "/core/upgrade/upgrade_domains.php";
     * But it checks permissions which we don't need when creating a team. So I copy only needed part from the file above
     *
     * @param   App\Models\Domain  $domain
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

      $settings = new DefaultSetting;
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

    public function update($userId, array $data, $options = [])
    {
        $user = $this->getRequestedUser($userId);

        $this->database->beginTransaction();

        try {
            $this->userRepository->update($user, $data);

            $this->dispatcher->dispatch(new UserWasUpdated($user));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $user;
    }

    public function delete($userId, $options = [])
    {
        $user = $this->getRequestedUser($userId);

        $this->database->beginTransaction();

        try {
            $this->userRepository->delete($userId);

            $this->dispatcher->dispatch(new UserWasDeleted($user));
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
}
