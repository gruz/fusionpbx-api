<?php

namespace Api\User\Services;

use Exception;
use Illuminate\Support\Arr;
use Api\User\Services\UserService;
use Api\User\Events\TeamWasCreated;
use Api\User\Events\UserWasDeleted;
use Api\User\Events\UserWasUpdated;
use Api\Domain\Services\DomainService;
use Api\Domain\Events\DomainWasCreated;
use Api\User\Repositories\ContactRepository;
use Api\Voicemail\Services\VoicemailService;
use Illuminate\Database\Eloquent\Collection;
use Api\Domain\Repositories\DomainRepository;
use Api\Domain\Services\DomainSettingService;
use Api\User\Exceptions\InvalidGroupException;
use Api\Domain\Exceptions\DomainExistsException;
use Api\User\Repositories\ContactUserRepository;
use Api\Dialplan\Repositories\DialplanRepository;
use Api\Extension\Repositories\ExtensionRepository;
use Infrastructure\Database\Eloquent\AbstractService;
use Api\Extension\Repositories\ExtensionUserRepository;
use Illuminate\Contracts\Container\BindingResolutionException;

class TeamService extends AbstractService
{
    private $domainService;

    private $dialplanRepository;

    private $domainSettingService;

    private $userService;

    private $voicemailService;

    public function __construct(
        DomainService $domainSevice,
        DialplanRepository $dialplanRepository,
        DomainSettingService $domainSettingService,
        UserService $userService,
        VoicemailService $voicemailService
    ) {
        $this->domainService = $domainSevice;
        $this->dialplanRepository = $dialplanRepository;
        parent::__construct();
        $this->domainSettingService = $domainSettingService;
        $this->userService = $userService;
        $this->voicemailService = $voicemailService;
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

        $data['domain_description'] =  Arr::get($data, 'domain_description', config('fpbx.domain.description'));

        return $data;
    }

    private function injectData($data, $inject)
    {
        foreach ($data as $key => $value) {
            $data[$key] = array_merge($value, $inject);
        }

        return $data;
    }

    public function create($data)
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
            $domainModel = $this->domainService->create($data);

            $settingsData = Arr::get($data, 'settings', []);
            $settingsData = $this->injectData($settingsData, ['domain_uuid' => $domainModel->domain_uuid]);
            $this->domainSettingService->createMany($settingsData);

            $usersData = Arr::get($data, 'users', []);
            $usersData = $this->injectData($usersData, ['domain_uuid' => $domainModel->domain_uuid]);
            $usersModel = $this->userService->createMany($usersData);

            foreach ($usersModel as $k => $userModel) {
                $contactsData = Arr::get($data, 'users.' . $k . '.contacts', []);
                $extensionData = Arr::get($data, 'users.' . $k . '.extensions', []);
                // $contactsModel = $this->contactService->createMany($contactsData, [
                //     'domain_uuid' => $domainModel->domain_uuid,
                //     'user_uuid' => $userModel->user_uuid,
                // ]);

                // $this->injectCommonData

                // foreach ($relatedData as $key => $data) {
                //     $relatedData[$key] = array_merge($data, $injectFields);
                // }

                $this->userService->createAttachedMany($userModel, ContactRepository::class, $contactsData, ContactUserRepository::class);
                $this->userService->createAttachedMany($userModel, ExtensionRepository::class, $extensionData, ExtensionUserRepository::class);

                $voicemailData = Arr::get($data, 'users.' . $k . '.extension', []);
                foreach ($voicemailData as $key => $value) {
                    $voicemailData[$key]['domain_uuid'] = $domainModel->domain_uuid;
                }
                $this->voicemailService->createMany($voicemailData);
                // $this->userRepository->attachModel($userModel, $contactsModel);

                // $contact_usersData = [];
                // foreach ($contactsModel as $v => $contactModel) {
                //     $contact_usersData[] = [
                //         // 'contact_user_uuid' => Str::uuid(),
                //         'domain_uuid' => $domainModel->domain_uuid,
                //         'user_uuid' => $userModel->user_uuid,
                //         'contact_uuid' => $contactModel->contact_uuid,
                //     ];
                // }

                // $extensionsData = Arr::get($data, 'users.' . $k . 'extensions', []);
                // $contacts = $this->extensionService->createMany($extensionsData, [
                //     'domain_uuid' => $domainModel->domain_uuid,
                //     'user_uuid' => $userModel->user_uuid,
                // ]);
            }

            // $data['domain_uuid'] = $domain->getAttribute('domain_uuid');

            // $userDataForResponse = [];

            // $users = collect(Arr::get($data, 'users'));
            // foreach ($users as $userData) {
            //     $user = $this->userService->create($userData, $data['domain_uuid']);
            //     $isAdmin = Arr::get($userData, 'is_admin', false);
            //     if ($isAdmin) {
            //         $domain->setRelation('admin_user', $user);
            //     }
            //     $userDataForResponse[] = [
            //         'domain_name' => $data['domain_name'],
            //         'username' => $userData['username'],
            //         'password' => $userData['password']
            //     ];
            // }

            $domainModel->message = __(
                'messages.team created',
                // $userDataForResponse
                []
            );
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        $this->dispatcher->dispatch(new TeamWasCreated($domainModel, $usersModel));
        // $this->dispatcher->dispatch(new DomainWasCreated($domain, true));

        return $domainModel;
    }

    /**
     * @deprecated Will be removed because of adding in a new way // ##mygruz20210130124229
     */
    public function createDeperacted($data)
    {
        $this->database->beginTransaction();

        try {
            if ($this->domainRepository->getWhere('domain_name', $data['domain_name'])->count() > 0) {
                throw new DomainExistsException();
            }

            $data['domain_enabled'] =  'true';
            $data['domain_description'] =  'Created via api at ' . date('Y-m-d H:i:s', time());

            $domain = $this->domainService->create($data);

            $this->dialplanRepository->createDefaultDialplanRules($data);

            $data['domain_uuid'] = $domain->getAttribute('domain_uuid');

            $user = $this->userService->create($data);


            // fuda :
            //      Maybe set relation ? (User->Domain)

            // ~ $data = array_merge($data, $user);
            /*
            $data['contact_type'] = 'user';
            $data['contact_nickname'] = $data['email'];


            $contact = $this->contactRepository->create($data);
            $contact->makeHidden(['domain_uuid']);
            $data['contact_uuid'] = $contact->getAttribute('contact_uuid');

            $data['email_primary'] = 1;
            $data['email_address'] = $data['email'];

            $contact_email = $this->contact_emailRepository->create($data);
            $contact_email->makeHidden(['domain_uuid', 'contact_uuid']);

            // ~ $data['username'] = $data['email'];
            $data['user_enabled'] = 'true';
            // $data['add_user'] = 'admin';
            // $data['add_date'] = 'admin';

            $user = $this->userRepository->create($data);
            $user->makeHidden(['domain_uuid', 'contact_uuid']);

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
            // ~ $this->dispatcher->dispatch(new TeamWasCreated($domain));

        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        $this->dispatcher->dispatch(new DomainWasCreated($domain, true));

        // $this->runFusionPBX_upgrade_domains($domain);

        return $domain;
    }

    /**
     * TODO Name or short description
     *
     * Here Gruz'd call
     * require_once app('fpath') . "/core/upgrade/upgrade_domains.php";
     * But it checks permissions which we don't need when creating a team. So I copy only needed part from the file above
     *
     * @param   Api\Domain\Models\Domain  $domain
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

    public function update($userId, array $data)
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

    public function delete($userId)
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
