<?php

namespace Api\Domain\Testing\Feature;

use Infrastructure\Testing\TestCase;
use Api\User\Models\User;
use Api\User\Models\Contact;
use Api\Domain\Models\Domain;
use Api\Settings\Models\Setting;
use Api\Extension\Models\Extension;
use Api\Voicemail\Models\Voicemail;
use Illuminate\Database\Eloquent\Factories\Sequence;

class DomainSignupTest extends TestCase
{
    private function makeDomainRequest() {
        $settings = Setting::factory(2)->make()->toArray();

        $is_admin = new Sequence(
            ['is_admin' => true],
            ['is_admin' => false],
        );

        $users = User::factory(3)
                ->state($is_admin)
                ->state(function (array $attributes) {return [
                    'contacts' => Contact::factory(2)
                        ->make()
                        ->makeVisible('password')
                        ->toArray(),
                ];})
                ->state(function (array $attributes) {
                    $extensions = [];
                    for ($i=0; $i < rand(1,4); $i++) {
                        $extension = Extension::factory(1)->make()->first()->toArray();
                        $voicemail = Voicemail::factory(1)->make()->first()->toArray();

                        $extensions[] = array_merge(
                            $extension,
                            $voicemail
                        );
                    }
                    // dd($extension, $voicemail);
                    return  [
                    'extensions' => $extensions];
                })
                ->make()
                ->makeVisible('password')
                ->toArray();

        $model = Domain::factory()->make([
            'settings' => $settings,
            'users' => $users,
        ]);

        return $model->toArray();
    }
    public function test_Adding_new_domain_passes_with_one_admin_user_passes() {
        $data = $this->makeDomainRequest();

        $domain['domain_name'] = $data['domain_name'];

        $this->json('post', 'api/domain/signup/', $data)
            ->assertStatus(200)
            ->assertDatabaseHas('v_domains', $data)
        ;
    }

    public function adding_new_domain_passes_with_several_admin_users_passes() {}


    public function test_Adding_existsing_domain_fails() {
        $response = $this->get('/');

        $response->assertStatus(200);

        $this->assertTrue(true);
    }

    public function adding_domain_with_no_or_bad_referral_code_fails() {
        $data = [
            'domain_name' => generate(),
            //
        ];

        $response = $this->post('/domain/signup', $data);

        $response->assertStatus(400);

        $data = [
            'domain_name' => generate(),
            'rereral_code' => 'bad',
            //
        ];

        $response = $this->post('/domain/signup', $data);

        $response->assertStatus(400);
    }

    public function adding_domain_with_missing_admin_user_fails() {}
    public function adding_domain_with_missing_users_fails() {}
    public function badPassword_adding_domain_with_missing_users_fails() {}
    public function duplicated_username_fails() {}
}
