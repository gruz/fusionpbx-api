<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Arr;
use App\Models\Contact;
use App\Models\Extension;
use App\Models\Voicemail;

trait UserTrait
{
    private function checkContactsCreated($domain, $userData)
    {
        $contacts = Arr::get($userData, 'contacts', []);
        foreach ($contacts as $contactData) {
            $this->assertDatabaseHas('v_contacts', array_merge(
                ['domain_uuid' => $domain->domain_uuid],
                $contactData
            ));

            $contactsModel = Contact::where(array_merge(
                ['domain_uuid' => $domain->domain_uuid],
                $contactData
            ));

            foreach ($contactsModel as $contactModel) {
                $this->assertDatabaseHas('v_contact_users', [
                    'domain_uuid' => $domain->domain_uuid,
                    'user_uuid' => $contactModel->user_uuid,
                    'contact_uuid' => $contactModel->contact_uuid,
                ]);
            }
        }
    }

    private function checkExtensionsCreated($domain, $userData, $modelClass)
    {
        $extensions = Arr::get($userData, 'extensions', []);
        /**
         * @var \App\Models\AbstractModel
         */
        $model = new $modelClass();
        $table = $model->getTable();
        foreach ($extensions as $extensionData) {
            if ('v_voicemails' === $table) {
                $extensionData['voicemail_id'] = $extensionData['extension'];
            }
            $tableColumns = $model->getTableColumnsInfo(true);
            $where =  [
                'domain_uuid' => $domain->domain_uuid
            ];
            foreach ($tableColumns as $columnName => $obj) {
                if (array_key_exists($columnName, $extensionData)) {
                    $where[$columnName] = $extensionData[$columnName];
                }
            }
            switch ($table) {
                case 'v_voicemails':
                    $this->assertNotEmpty($where['voicemail_password']);
                    break;
                case 'v_extensions':
                    $this->assertNotEmpty($where['password']);
                    unset($where['enabled']);
                    break;
                default:
                    $this->assertNotEmpty($where['password']);
                    break;
            }
            $this->assertDatabaseHas($table, $where);
        }
    }

    protected function prepareNonExistingEmailInDomain($domain_uuid)
    {
        $nonExistingEmail = $this->faker->email;
        while (true) {
            $user = User::where('domain_uuid', $domain_uuid)
                ->where('user_email', $nonExistingEmail)
                ->first();
            if (empty($user)) {
                break;
            } else {
                $nonExistingEmail = $this->faker->email;
            }
        }

        return $nonExistingEmail;
    }

    private function checkUserCreated($domain, $userData)
    {
        $userWhere = $this->createUserWhereConditions($userData);
        // dd($userWhere);
        $this->assertDatabaseHas('v_users', array_merge(
            ['domain_uuid' => $domain->domain_uuid],
            $userWhere
        ));
    }

    private function checkGroupCreated($domain, $userData)
    {
        $isAdmin = Arr::get($userData, 'is_admin');
        $userWhere = $this->createUserWhereConditions($userData);
        $userModel = User::where(array_merge(
            ['domain_uuid' => $domain->domain_uuid],
            $userWhere
        ))->first();
        $groupName = $isAdmin
            ? config('fpbx.default.user.group.admin')
            : config('fpbx.default.user.group.public');

        $this->assertDatabaseHas('v_groups', [
            'group_name' => $groupName,
        ]);
        $groupModel = Group::where('group_name', $groupName)->first();
        $this->assertDatabaseHas('v_user_groups', [
            'domain_uuid' => $domain->domain_uuid,
            'user_uuid' => $userModel->user_uuid,
            'group_uuid' => $groupModel->group_uuid,
            'group_name' => $groupModel->group_name,
        ]);
    }

    private function createUserWhereConditions($userData)
    {
        $userModel = new User();
        $userExceptColumns = array_merge($userModel->getGuarded(), ['password', 'user_uuid']);
        $userTableColumns = Arr::except($userModel->getTableColumnsInfo(true), $userExceptColumns);
        foreach ($userTableColumns as $columnName => $obj) {
            if (array_key_exists($columnName, $userData)) {
                $userWhere[$columnName] = $userData[$columnName];
            }
        }

        return $userWhere;
    }

    private function checkUserSettingCreated($domain, $userData)
    {
        // dd($userData);
        $userSettings = Arr::get($userData, 'user_settings', []);
        if (empty($userSettings)) {
            return;
        }
        $userWhere = $this->createUserWhereConditions($userData);
        $userModel = User::where(array_merge(
            ['domain_uuid' => $domain->domain_uuid],
            $userWhere
        ))->first();

        foreach ($userSettings as $key => $userSetting) {
            $this->assertDatabaseHas('v_user_settings', [
                'domain_uuid' => $domain->domain_uuid,
                'user_uuid' => $userModel->user_uuid,
                'user_setting_category' => $userSetting['user_setting_category'],
                'user_setting_subcategory' => $userSetting['user_setting_subcategory'],
                'user_setting_value' => $userSetting['user_setting_value'],
            ]);
        }
    }

    protected function checkUserWithRelatedDataCreated($domain, $userData)
    {
        $this->checkUserCreated($domain, $userData);
        $this->checkContactsCreated($domain, $userData);
        $this->checkExtensionsCreated($domain, $userData, Extension::class);
        $this->checkExtensionsCreated($domain, $userData, Voicemail::class);
        $this->checkGroupCreated($domain, $userData);
        $this->checkUserSettingCreated($domain, $userData);
    }
}
