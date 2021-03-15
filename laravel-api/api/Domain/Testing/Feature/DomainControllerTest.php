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

class DomainControllerTest extends TestCase
{
    public function testSignup_Success()
    {
        // $this->withoutExceptionHandling();
        // $this->expectException(\Exception::class);
        list($request, $response) = $this->simulateSignup();

        $this->assertDatabaseHas('postponed_actions', ['request->domain_name' => $request['domain_name']]);

        $users = collect(Arr::get($request, 'users'))->where('is_admin', true)->pluck('user_email')->toArray();

        // Assert a notification was sent to the given users...
        Notification::assertSentTo(
            new AnonymousNotifiable,
            DomainSignupNotification::class,
            function (DomainSignupNotification $notification, array $channels, AnonymousNotifiable $notifiable) use (&$users) {
                if (!in_array($notifiable->routes['mail'], $users)) {
                    return false;
                }

                $email = $notifiable->routes['mail'];

                $url = route('fpbx.get.domain.activate', [
                    'hash' => PostponedAction::first()->hash,
                    'email' => $email,
                ]);
                $recepient = new stdClass;
                $recepient->email = $email;
                $mail = $notification->toMail($recepient);
                $this->assertEquals($mail->actionUrl, $url);

                if (($key = array_search($email, $users)) !== false) {
                    unset($users[$key]);
                }

                return true;
            }
        );

        $this->assertEmpty($users, 'Not all users notified :' . print_r($users, true));

        $response->assertStatus(201);
    }

    public function atestActivate_Failed()
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

    public function atest_Activate_Success()
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
}