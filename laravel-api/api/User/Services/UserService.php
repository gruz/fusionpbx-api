<?php

namespace Api\User\Services;

use Exception;
use Api\User\Models\Group;
use Illuminate\Support\Arr;
use Api\Extension\Models\Extension;
use Illuminate\Support\Facades\Auth;
use Api\User\Events\UserWasActivated;
use Api\User\Services\ContactService;
use Api\Domain\Services\DomainService;
use Api\User\Repositories\UserRepository;
use Api\User\Repositories\GroupRepository;
use Api\Extension\Services\ExtensionService;
use Api\User\Repositories\ContactRepository;
use Api\Voicemail\Services\VoicemailService;
use Api\Domain\Repositories\DomainRepository;
use Infrastructure\Traits\OneToManyRelationCRUD;
use Api\User\Repositories\ContactEmailRepository;
use Api\Extension\Repositories\ExtensionRepository;
use Infrastructure\Database\Eloquent\AbstractService;

class UserService extends AbstractService
{
    use OneToManyRelationCRUD;

    private $groupRepository;

    private $userRepository;

    private $contactRepository;

    /**
     * @var ContactEmailRepository
     */
    private $contactEmailRepository;

    private $extensionRepository;

    private $domainRepository;

    private $extensionService;

    private $scope;

    private $domainService;

    private $userSettingService;

    public function __construct(
        DomainService $domainService,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        ContactRepository $contactRepository,
        ContactEmailRepository $contactEmailRepository,
        ExtensionRepository $extensionRepository,
        DomainRepository $domainRepository,
        ExtensionService $extensionService,
        VoicemailService $voicemailService,
        ContactService $contactService,
        UserSettingService $userSettingService
    ) {
        $this->domainService = $domainService;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->contactEmailRepository = $contactEmailRepository;
        $this->extensionRepository = $extensionRepository;
        $this->domainRepository = $domainRepository;
        $this->extensionService = $extensionService;
        $this->voicemailService = $voicemailService;
        $this->contactService = $contactService;
        $this->userSettingService = $userSettingService;

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
        if ($user = Auth::user()) {
            $data['add_user'] = $user->username;
        } else {
            $data['add_user'] = config('fpbx.default.user.creatorName');
        }

        return $data;
    }

    public function createMany($data, $options = [])
    {
        $this->database->beginTransaction();

        $models = [];

        try {
            foreach ($data as $key => $row) {
                $model = $this->create($row, $options);
                $models[] = $model;
            }
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $models;
    }


    public function create($data, $options = [])
    {
        $data = $this->prepareData($data);

        $this->database->beginTransaction();

        try {
            $domain_uuid = Arr::get($data, 'domain_uuid', null);

            if (empty($domain_uuid)) {
                $domainModel = $this->domainService->getByAttributes([
                    'domain_name' => $data['domain_name'],
                    'domain_enabled' => true,
                ])->first();
                $domain_uuid = $domainModel->domain_uuid;
            }

            $data['domain_uuid'] = $domain_uuid;
            $userModel = $this->repository->create($data);

            $this->addRelations($userModel, $data, 'contacts', $this->contactService);
            $this->addRelations($userModel, $data, 'extensions', $this->extensionService);

            $reseller_reference_code = Arr::get($data, 'reseller_reference_code');

            if ($reseller_reference_code) {
                $userSettings = Arr::get($data, 'user_settings', []);
                $userSettings[] = [
                    "user_setting_category" => "payment",
                    "user_setting_subcategory" => "reseller_code",
                    "user_setting_name" => "text",
                    "user_setting_value" => $reseller_reference_code,
                    "user_setting_order" => 0,
                    "user_setting_enabled" => true,
                    "user_setting_description" => 'Reseller code used for payment',
                ];
                // Arr::set($data, 'user_settings', $userSettings);
                // $this->addRelations($userModel, $data, 'user_settings', $this->userSettingService);
                // $settingsData = Arr::get($data, 'settings', []);
                $userSettings = $this->injectData($userSettings, [
                    'user_uuid' => $userModel->getAttribute('user_uuid'),
                    'domain_uuid' => $userModel->domain->getAttribute('domain_uuid'),
                ]);
                $this->userSettingService->createMany($userSettings, ['forceFillable' => ['domain_uuid', 'user_uuid']]);
            }

            $isAdmin = Arr::get($data, 'is_admin', false);
            // $groupName = config('fpbx.default.user.group.public');
            // needs to be discussed what to do with other groups
            $groupName = $isAdmin
                ? config('fpbx.default.user.group.admin')
                : config('fpbx.default.user.group.public');
            $relatedModel = Group::where('group_name', $groupName)->first();
            $this->setRelation($userModel, $relatedModel, ['group_name' => $groupName]);

            $extensionsData = Arr::get($data, 'extensions', []);
            $extensionsData = $this->injectData($extensionsData, ['domain_uuid' => $domain_uuid]);
            $voicemailData = $extensionsData;
            foreach ($voicemailData as $key => $value) {
                $voicemailData[$key]['voicemail_id'] = $voicemailData[$key]['extension'];
            }

            $this->voicemailService->createMany($voicemailData, ['forceFillable' => ['domain_uuid', 'voicemail_id']]);

            $this->dispatchEvent('Created', $userModel, $options);
            // dd('added user');
        } catch (Exception $e) {
            $this->database->rollBack();
            throw $e;
        }

        $this->database->commit();

        return $userModel;
    }

    public function getMe($options = [])
    {
        //return Auth::user();
        $class = Extension::class;
        $class::$staticMakeVisible = ['password'];
        return $this->userRepository->getWhere('user_uuid', Auth::user()->user_uuid)->first();
    }

    public function activate($hash, $sendNotification = true)
    {
        // Since there is no a field dedicated to activation, Gruz have decided to use the quazi-boolean user_enabled field.
        // FusionPBX recognizes non 'true' as FALSE. So our hash in the user_enabled field is treated as FALSE till user is activated.

        // if (!Str::isUuid($hash)) {
        //     throw new ActivationHashWrongException();
        // }

        $this->database->beginTransaction();

        try {
            $user = $this->userRepository->getWhere('user_enabled', $hash)->first();

            $user->user_enabled = 'true';

            $user->save();

            $this->dispatcher->dispatch(new UserWasActivated($user, $sendNotification));
        } catch (Exception $e) {
            $this->database->rollBack();
            throw $e;
        }

        $this->database->commit();

        $response = [
            'message' => __('User activated'),
            'user' => $user
        ];

        return $response;
    }

    public function getUserByEmailAndDomain($user_email, $domain_name) {
        return $this->userRepository->getUserByEmailAndDomain($user_email, $domain_name);
    }

    private function addRelations($userModel, $data, $type, $service) {

        $relatedData = Arr::get($data, $type, []);
        $relatedData = $this->injectData($relatedData, ['domain_uuid' => $userModel->getAttribute('domain_uuid')]);

        foreach ($relatedData as $row) {
            $relatedModel = $service->create($row, ['forceFillable' => ['domain_uuid']]);
            $this->setRelation($userModel, $relatedModel);
        }
    }
}
