<?php

namespace Api\Settings\Services;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Api\Settings\Exceptions\InvalidSettingException;
use Api\Settings\Exceptions\SettingNotFoundException;
use Api\Settings\Events\SettingWasCreated;
use Api\Settings\Events\SettingWasDeleted;
use Api\Settings\Events\SettingWasUpdated;
use Api\Settings\Repositories\SettingRepository;

class SettingService
{
    private $auth;

    private $database;

    private $dispatcher;

    // ~ private $roleRepository;

    private $settingRepository;

    public function __construct(
        AuthManager $auth,
        DatabaseManager $database,
        Dispatcher $dispatcher,
        // ~ GroupRepository $roleRepository,
        SettingRepository $settingRepository
    ) {
        $this->auth = $auth;
        $this->database = $database;
        $this->dispatcher = $dispatcher;
        // ~ $this->roleRepository = $roleRepository;
        $this->extensionRepository = $settingRepository;
    }

    public function getAll($options = [])
    {
        $user = $this->auth->user();

        return $this->extensionRepository->getWhereArray(['domain_uuid' => $user->domain_uuid]);
    }

    public function getById($settingId, array $options = [])
    {
        $setting = $this->getRequestedSetting($settingId);

        return $setting;
    }

    public function create($data)
    {
        $this->database->beginTransaction();

        try {
            $setting = $this->extensionRepository->create($data);

            $this->dispatcher->fire(new SettingWasCreated($setting));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $setting;
    }

    public function update($settingId, array $data)
    {
        $setting = $this->getRequestedSetting($settingId);

        $this->database->beginTransaction();

        try {
            $this->extensionRepository->update($setting, $data);

            $this->dispatcher->fire(new SettingWasUpdated($setting));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $setting;
    }

    public function delete($settingId)
    {
        $setting = $this->getRequestedSetting($settingId);

        $this->database->beginTransaction();

        try {
            $this->extensionRepository->delete($settingId);

            $this->dispatcher->fire(new SettingWasDeleted($setting));
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
