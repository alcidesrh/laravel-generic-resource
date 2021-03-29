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
        // $configPath = __DIR__.'/../config/dompdf.php';
        // $this->mergeConfigFrom($configPath, 'dompdf');

        // $this->app->bind('dompdf.options', function(){
        //     $defines = $this->app['config']->get('dompdf.defines');

        //     if ($defines) {
        //         $options = [];
        //         foreach ($defines as $key => $value) {
        //             $key = strtolower(str_replace('DOMPDF_', '', $key));
        //             $options[$key] = $value;
        //         }
        //     } else {
        //         $options = $this->app['config']->get('dompdf.options');
        //     }

        //     return $options;

        // });

        // $this->app->bind('dompdf', function() {

        //     $options = $this->app->make('dompdf.options');
        //     $dompdf = new Dompdf($options);
        //     $dompdf->setBasePath(realpath(base_path('public')));

        //     return $dompdf;
        // });
        // $this->app->alias('dompdf', Dompdf::class);

        // $this->app->bind('dompdf.wrapper', function ($app) {
        //     return new PDF($app['dompdf'], $app['config'], $app['files'], $app['view']);
        // });

    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/route.php');

        $this->publishes([
            __DIR__.'/config.php' => config_path('generic-resource.php'),
        ]);
    }

}
