<?php
namespace Alcidesrh\Generic;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register the service provider.
     *
     * @throws \Exception
     * @return void
     */
    public function register()
    {

    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/route.php');

        $this->publishes([
            __DIR__.'/generic-resource.php' => config_path('generic-resource.php'),
        ]);
    }

}
