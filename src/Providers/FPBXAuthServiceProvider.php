<?php

namespace Gruz\FPBX\Providers;

use Illuminate\Support\Str;
use Gruz\FPBX\Models\AbstractModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Gruz\FPBX\Models\GroupPermission;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class FPBXAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerGates();

        $this->remakeVeirificationEmail();
    }

    private function remakeVeirificationEmail() {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $value = $notifiable->getAttribute('user_enabled');
            $value = explode('::', $value);
            $verificationCode = $value[0];

            return $url = route('fpbx.user.activate', [
                'user_uuid' => $notifiable->getKey(),
                'user_enabled' => $verificationCode
            ]);
        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $value = $notifiable->getAttribute('user_enabled');
            $value = explode('::', $value);
            $verificationCode = $value[0];
            return (new MailMessage)
                ->subject(Lang::get('Verify Email Address'))
                ->line(__('Use validation code **:code**', ['code' => $verificationCode]))
                ->line(__('or press the button below'))
                ->action(Lang::get('Verify Email Address'), $url)
                ->line(Lang::get('If you did not create an account, no further action is required.'));

        });
    }

    private function registerGates() {
        /**
         * @var GroupPermission
         */
        $model = app(GroupPermission::class);
        $permissions = $model->select('permission_name')->groupBy('permission_name')->orderBy('permission_name')->get()->pluck('permission_name')->toArray();
        foreach ($permissions as $permission_name) {
            // if (strpos($permission_name,'user') !== 0) {
            //     continue;
            // }
            // if ($existingModel = $this->getModelFromPermissionName($permission_name)) {
                // dump($permission_name, $existingModel);
                Gate::define($permission_name, function (\Gruz\FPBX\Models\User $user, AbstractModel $model) use ($permission_name) {

                    static $userPermissionsCollection = [];

                    if (empty($userPermissionsCollection[$user->user_uuid])) {
                        $userPermissionsCollection[$user->user_uuid] = $user->permissions;
                    }

                    $hasUserUUID = in_array('user_uuid', $model->getTableColumnNames(true));

                    if ($hasUserUUID && $user->user_uuid === $model->user_uuid) {
                        return true;
                    }

                    $count = $userPermissionsCollection[$user->user_uuid]
                        ->where('permission_name', $permission_name)
                        ->where('permission_assigned', 'true')
                        ->count()
                        ;

                    return $count > 0;

                });
            // }
        }
    }

    private function getModelFromPermissionName($permission_name) {
        static $models = [];

        if (empty($models)) {
            $path = __DIR__ . '/../Models/';
            $files = File::files($path);
            foreach ($files as $file) {
                $className = basename($file->getFilename(), '.php');
                if ('AbstractModel' === $className) {
                    continue;
                }
                $filename = Str::snake($className);
                $className = 'Gruz\\FPBX\\Models\\' . $className;
                $models[$filename] = $className;
            }
        }

        $permission_parts = explode('_', $permission_name);

        while(!empty($permission_parts)) {
            $tryModelName = implode('_', $permission_parts);
            if (array_key_exists($tryModelName, $models)) {
                return $models[$tryModelName];
            }
            $tryModelName = Str::singular($tryModelName);
            if (array_key_exists($tryModelName, $models)) {
                return $models[$tryModelName];
            }
            array_pop($permission_parts);
        }

        return false;
    }
}
