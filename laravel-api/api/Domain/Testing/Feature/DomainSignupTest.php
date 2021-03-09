<?php

namespace Api\Domain\Testing\Feature;

use stdClass;
use Api\User\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Api\Domain\Models\Domain;
use Infrastructure\Testing\TestCase;
use Illuminate\Support\Facades\Notification;
use Api\PostponedAction\Models\PostponedAction;
use Illuminate\Notifications\AnonymousNotifiable;
use Api\Domain\Notifications\DomainSignupNotification;
use Infrastructure\Services\TestRequestFactoryService;

class DomainSignupTest extends TestCase
{
    /**
     * @var TestRequestFactoryService
     */
    private $testRequestFactoryService;

    public function setUp(): void
    {

        parent::setUp();

        $this->testRequestFactoryService = app(TestRequestFactoryService::class);
    }

    private function simulateSignup()
    {
        PostponedAction::query()->truncate();
        Notification::fake();

        $data = $this->testRequestFactoryService->makeDomainRequest();
        $response = $this->json('post', route('fpbx.post.domain.signup'), $data);

        return [$data,  $response];
    }

    public function test_Signup()
    {
        // $this->withoutExceptionHandling();
        // $this->expectException(\Exception::class);
        list($request, $response) = $this->simulateSignup();

        // \Illuminate\Support\Arr::set($data, 'users.0.user_email', 'a@a.com');
        // \Illuminate\Support\Arr::set($data, 'users.1.user_email', 'a@a.com');
        // \Illuminate\Support\Arr::set($data, 'users.0.is_admin', false);
        // \Illuminate\Support\Arr::set($data, 'users.1.is_admin', false);
        // \Illuminate\Support\Arr::set($data, 'users.2.is_admin', false);

        // $data['domain_name'] = '192.168.0.160';


        // Don't delete, for getting JSON requests as example
        // \Illuminate\Support\Facades\Storage::put('request.json', json_encode($data, JSON_PRETTY_PRINT));

        $this->assertDatabaseHas('postponed_actions', ['request->domain_name' => $request['domain_name']]);

        $users = [];
        foreach (Arr::get($request, 'users') as $user) {
            $recepient = new stdClass;

            if (Arr::get($user, 'is_admin', false)) {
                $recepient->name = Arr::get($user, 'username');
                $recepient->email = Arr::get($user, 'user_email');
                $users[] = $recepient;
            }
        }

        // Assert a notification was sent to the given users...
        Notification::assertSentTo(
            new AnonymousNotifiable,
            DomainSignupNotification::class,
            function (DomainSignupNotification $notification, array $channels, AnonymousNotifiable $notifiable) use ($users) {
                $url = route('fpbx.get.domain.activate', ['hash' => PostponedAction::first()->hash]);
                $mail = $notification->toMail($users[0]);
                $this->assertEquals($mail->actionUrl, $url);
                // We cannot use === as here comparison doesn't work https://stackoverflow.com/a/66511294/518704
                return $notifiable->routes['mail'] == $users;
            }
        );

        // $response->dump();
        // dd($response);
        $response->assertStatus(201);
    }

    public function test_EmailLinkVerificationFailed()
    {
        // $this->withoutExceptionHandling();
        // list($request, $response) = $this->simulateSignup();
        $this->simulateSignup();

        $model = PostponedAction::first();
        $domain_name = $model->getAttribute('request->domain_name');

        // Bad hash in link
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash]) . 'aa');
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('validation.uuid'));

        // Not hash exists in the table
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => Str::uuid()]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('validation.exists', ['attribute' => 'hash']));

        // Trying to add already existing domain
        $model->setAttribute('request->domain_name', Domain::first()->getAttribute('domain_name'));
        $model->save();
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('Domain already exists'));

        // Check link is expired

        // Restore domain from request
        $model->setAttribute('request->domain_name', $domain_name);

        // Simulate outdated link
        $model->created_at = $model->created_at->sub('1 year');
        $model->save();

        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('Domain activation link expired'));
    }

    public function test_DomainActivate()
    {
        $this->simulateSignup();

        $model = PostponedAction::first();
        $domain_name = $model->getAttribute('request->domain_name');

        $data = collect($model->request);
        $requestUsers = collect($data->get('users'));

        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash]));
        $response->assertStatus(201);

        $this->assertDatabaseHas(Domain::class, ['domain_name' => $domain_name]);

        $domain = Domain::where('domain_uuid', $domain_name)->first();
        $user = User::where(['domain_name' => $domain->domain_uuid]);

        $this->assertEquals($requestUsers->count(), $user->count());

        $dialplan_dest_folder = config('app.fpath_document_root') . '/opt-laravel-api';
        $this->assertDirectoryExists($dialplan_dest_folder);




        $domain = User::where('domain_uuid', $domain->domain_uuid);
        $this->assertDatabaseHas(User::class, ['domain_name' => $domain_name]);


        $users = [];
        foreach (Arr::get($request, 'users') as $user) {
            $recepient = new stdClass;

            if (Arr::get($user, 'is_admin', false)) {
                $recepient->name = Arr::get($user, 'username');
                $recepient->email = Arr::get($user, 'user_email');
                $users[] = $recepient;
            }
        }

        // Assert a notification was sent to the given users...
        Notification::assertSentTo(
            new AnonymousNotifiable,
            DomainSignupNotification::class,
            function (DomainSignupNotification $notification, array $channels, AnonymousNotifiable $notifiable) use ($users) {
                $url = route('fpbx.get.domain.activate', ['hash' => PostponedAction::first()->hash]);
                $mail = $notification->toMail($users[0]);
                $this->assertEquals($mail->actionUrl, $url);
                // We cannot use === as here comparison doesn't work https://stackoverflow.com/a/66511294/518704
                return $notifiable->routes['mail'] == $users;
            }
        );
    }

    public function test_Adding_new_domain_passes_with_several_admin_users_passes()
    {
    }


    public function test_Adding_existsing_domain_fails()
    {
        // $response = $this->get('/');

        // $response->assertStatus(200);

        // $this->assertTrue(true);
    }

    public function test_Adding_domain_with_no_or_bad_referral_code_fails()
    {
        return;
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

    public function adding_domain_with_missing_admin_user_fails()
    {
    }
    public function adding_domain_with_missing_users_fails()
    {
    }
    public function badPassword_adding_domain_with_missing_users_fails()
    {
    }
    public function duplicated_username_fails()
    {
    }
}
