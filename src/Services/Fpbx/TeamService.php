<?php

namespace Gruz\FPBX\Services\Fpbx;

use Exception;
use Illuminate\Support\Arr;
use Gruz\FPBX\Events\TeamWasCreated;
use Gruz\FPBX\Exceptions\DomainExistsException;
use Gruz\FPBX\Repositories\DomainRepository;
use Gruz\FPBX\Repositories\DialplanRepository;
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

        if (!config('fpbx.default.domain.enabled')) {
            $data['domain_enabled'] = false;
        } else {
            $data['domain_enabled'] = Arr::get($data, 'domain_enabled', config('fpbx.default.domain.enabled'));
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

        $refreshDisabled = config('disable_fpbx_refresh');

        if (!$refreshDisabled) {
            config(['disable_fpbx_refresh' => true]);
        }

        $this->database->beginTransaction();

        try {
            /**
             * @var DomainRepository
             */
            $domainRepository = $this->domainService->getRepository();
            if ($domainRepository->getWhere('domain_name', $data['domain_name'])->count() > 0) {
                throw new DomainExistsException();
            }

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

        if (!$refreshDisabled) {
            config(['disable_fpbx_refresh' => false]);
            app(\Gruz\FPBX\Services\FreeSwitchHookService::class)->reload();
        }

        return $domainModel;
    }
}
