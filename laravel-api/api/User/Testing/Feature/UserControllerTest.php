<?php

namespace Api\User\Testing\Feature;

use stdClass;
use Faker\Factory;
use Api\User\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Api\User\Models\Contact;
use Api\Domain\Models\Domain;
use Api\Extension\Models\Extension;
use Api\Voicemail\Models\Voicemail;
use Infrastructure\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Api\PostponedAction\Models\PostponedAction;
use Illuminate\Notifications\AnonymousNotifiable;
use Api\Domain\Notifications\DomainSignupNotification;
use Api\Domain\Notifications\DomainActivateActivatorNotification;
use Api\Domain\Notifications\DomainActivateMainAdminNotification;
use Api\User\Models\Group;
use Api\User\Notifications\UserWasCreatedSendVeirfyLinkNotification;

class UserControllerTest extends TestCase
{
    public function test_ForgotPassword_Success()
    {
        // logic when test have to be passed 
        // (e.x. correct email + domain + user + evth exists etc ...)
    }

    public function test_ForgotPassword_Failed()
    {
        // all cases when test have to fail
    }
}
