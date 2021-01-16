<?php

use Api\User\Models\User;
use Api\Extension\Models\Extension;

$router->get('/', [\Infrastructure\Api\Controllers\DefaultApiController::class, 'index']);

// ~ Route::post(['middleware' => 'auth:api'], function() {
    // ~ Route::resource('users', 'User\UserController');
	// ~ });

// ~ $router->post('register', '\Api\User\Controllers\UserController@register');
$router->post('/signup', [\Api\User\Controllers\UserController::class, 'signup']);
// ~ $router->post('/activate/user/{}', '\Api\User\Controllers\UserController@activate');
// ~ $router->post('/create', '\Api\User\Controllers\UserController@create');
// ~ Route::resource('domain', '\Api\User\Controllers\DomainController');
// ~ Route::resource('user', '\Api\User\Controllers\UserController');

// TOOD 8
// \Illuminate\Support\Facades\Auth::routes(['verify' => true]);

$router->get('/foo', function () {
    $user = User::with('groups')
      ->get()->toArray();
dd($user);
    $current_path = getcwd();
    chdir(config('app.fpath_full'));
    exec('php ./core/upgrade/upgrade_domains.php', $result);
    chdir($current_path);


    // $user = User::where([
    //     'domain_uuid' => '3bbdb3b0-2bfc-11eb-b2c2-bd0cc0c82120',
    //   ])
    //   ->where('user_enabled', '!=', 'true')
    //   ->get()->toArray();


    // $user = Extension::with('users')
    //     ->find('38c0e870-1bac-11eb-83d2-bfed3ed44692')
//     select "v_users".*,
//  "v_extension_users"."user_uuid" as "pivot_user_uuid",
//  "v_extension_users"."extension_uuid" as "pivot_extension_uuid" 
//  from "v_users" 
//  inner join "v_extension_users" 
//  on "v_users"."user_uuid" = "v_extension_users"."extension_uuid" 
//  where "v_extension_users"."user_uuid" in (11)


    // $user = app(new ExtensionRepository)->getByID('38c0e870-1bac-11eb-83d2-bfed3ed44692', ['includes'=>'users'])
        // ->has('domain')
        // ->with('groups')
        // ->has('permissions')
    ;
    // dd($user);
});
