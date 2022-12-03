<?php

namespace App\Providers;

use App\Repositories\Cache\UserCacheRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class BindingServiceProvider extends ServiceProvider
{
    public $bindings = [
//        UserRepositoryInterface::class => UserCacheRepository::class, //binding interface with cache, if you use cache
        UserRepositoryInterface::class => UserRepository::class
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->bindings as $interface => $implement){
            $this->app->bind($interface, $implement);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
