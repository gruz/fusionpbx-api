<?php

namespace Infrastructure\Testing;

use Api\User\Models\User;
use Illuminate\Support\Arr;
use Api\User\Models\Contact;

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
         * @var \Infrastructure\Database\Eloquent\AbstractModel
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
                    # code...
                    $this->assertNotEmpty($where['voicemail_password']);
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
}
