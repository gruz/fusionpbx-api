<?php

namespace Api\User\Services;

use Infrastructure\Auth\Exceptions\InvalidCredentialsException;
use Api\User\Repositories\UserRepository;
use Api\Domain\Repositories\DomainRepository;
use Api\User\Repositories\Contact_emailRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Api\User\Exceptions\UserDisabledException;
use Api\User\Models\User;
use Webpatser\Uuid\Uuid;
use Api\User\Services\UserService;
use Api\Domain\Exceptions\DomainNotFoundException;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Hashing\Hasher; 


class UserPasswordService
{

    private $app;

    private $userRepository;

    private $domainRepository;

    private $contact_emailRepository;

    private $userService;

    private $hasher;

    public function __construct(
      UserRepository $userRepository,
      DomainRepository $domainRepository,
      Contact_emailRepository $contact_emailRepository,
      UserService $userService,
      Application $app,
      Hasher $hasher
    )
    {
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
        $this->contact_emailRepository = $contact_emailRepository;
        $this->userService = $userService;
        $this->app = $app;
        $this->hasher = $hasher;
    }

    /**
     * Method to generate token (that is necesary to reset password) and link that
     * will be sent to user via email to enable him to reset the password. 
     *
     * @param $data Contains: 
     *                  User email for which password needs to be reset.
     *                  Domain name to which user belongs.
     * @return array|mixed 
     * @throws InvalidCredentialsException|UserDisabledException
     */
    public function generateResetToken($data)
    {
        $domainName = $data['domain_name'];
        // $hashKey = $this->app['config']['app.key'];
        // if (Str::startsWith($hashKey, 'base64:')) {
        //     $hashKey = base64_decode(substr($hashKey, 7));
        // }
        $userCredentials = $this->getUserCredentials($data)->toArray();
        $status = Password::sendResetLink($userCredentials);
            // , function ($user, $token) use ($domainName) {
                    // DB::beginTransaction();
                    // try {
                    //     // $token_test =  hash_hmac('sha256', $token, $hashKey);
                    //     $hashKey =  $this->hasher->make($token);
                    //     // insert to DB table password_resets domain_name property 
                    //     DB::table('password_resets')->where('token', $hashKey)
                    //                                 ->update([
                    //                                     'domain_name' => $domainName,
                    //                                     'created_at' => Carbon::now()
                    //                                 ]);

                        // ResetPassword::createUrlUsing(function($notifiable, $token) use ($domainName)  {
                        //     return url(route('password.reset', [
                        //         'token' => $token,
                        //         'email' => $notifiable->getEmailForPasswordReset(),
                        //         'domain_name' => $domainName
                        //     ], false));
                        // });

                    //     // send reset link
                    //     $user->sendPasswordResetNotification($token);
                    // } catch (Exception $e) {
                    //     DB::rollback();
                    //     throw $e;
                    // }

                    // DB::commit();
            // });

        return [
            'username' => $userCredentials['username'],
            'domain_uuid' => $userCredentials['domain_uuid'],
        ];
    }

    /**
     * Method to reset user password based on user credentials.
     *
     * @param $data Data from request
     * @return array|mixed 
     * @throws InvalidCredentialsException|UserDisabledException
     */
    public function resetPassword($data) 
    {
        $userCredentials = array_merge($this->getUserCredentials($data)->toArray(), $data);
        $status = Password::reset(
            $userCredentials,
            function ($user, $password) {

                // $data['salt'] = Uuid::generate();
                // $data['password'] = md5($data['salt'] . $password);

                $data = \encrypt_password_with_salt($password);

                $user->password = $data['password'];
                $user->salt = $data['salt'];
                
                $user->fill($data);
                $user->save();
                $user->setRememberToken(Str::random(60));
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET )
            return null;

        return $status;
    }

    /**
     * Method to prepare user credential for password reset
     * If user user_email attribute is not set then we
     * should get user by domain and contact. 
     *
     * @param $data Contains user email and domain name to which user belongs
     * @return null|\Api\User\Models\User 
     * @throws InvalidCredentialsException|UserDisabledException
     */
    public function getUserCredentials($data)
    {
        $domain = $this->domainRepository
                            ->getWhere('domain_name', $data['domain_name'])->first();

        if (is_null($domain)) {
            throw new DomainNotFoundException();
        }
        
        $attributes = [
            'domain_uuid' => $domain->domain_uuid,
            'user_email' => $data['user_email'],
        ];
        
        $user = $this->userService->getByAttributes($attributes);

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
              throw new UserDisabledException();
            }

            return $user;
        }

        throw new InvalidCredentialsException(__('User doesn\'t exists'));
    }

    /**
     * Method to get user by email (user_email attribute).
     * 
     * @param $email User email
     * @return null|\Api\User\Models\User
     */
    public function getUserByEmail($email) 
    {
        // Search by v_user table in user_email field
        $user = $this->userRepository
                     ->getWhere('user_email', $email)
                     ->first();
        
        return $user;
    }

}