<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redis;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     if ($request->input('api_token')) {
        //         return User::where('api_token', $request->input('api_token'))->first();
        //     }
        // });
        $this->app['auth']->viaRequest('api', function ($request) {
            // if ($request->header('uuid')) {
            //     //$key = explode(' ', $request->header('Authorization'));
            //     $user = User::select('id','username')->where('id', $request->header('uuid'))->first();
            //     //$user = Users::where('api_key', $key[1])->first();
            //     // if (!empty($user)) {
            //     //     $request->request->add(['userid' => $user->id]);
            //     // }
            //     $request->request->add(['userid' => "80ffd4f5-fa3a-4ee8-a51c-dec558bf95b3"]);
            //     return $user;
            // }
            $user = User::select('id','username')->where('id', '80ffd4f5-fa3a-4ee8-a51c-dec558bf95b3')->first();
            $request->request->add(['userid' => "80ffd4f5-fa3a-4ee8-a51c-dec558bf95b3"]);
            return $user;
        });
    }
}
