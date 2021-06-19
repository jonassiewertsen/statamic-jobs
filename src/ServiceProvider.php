<?php

namespace Jonassiewertsen\Jobs;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
//            // Config
//            $this->publishes([
//                __DIR__ . '/../config/config.php' => config_path('jobs.php'), // Save inside statamic config files
//            ], 'jobs-config');

            // Blueprints
            $this->publishes([
                __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
            ], 'jobs-blueprints');

            // Collections
            $this->publishes([
                __DIR__.'/../resources/collections' => base_path('content/collections'),
            ], 'jobs-collections');
        }
    }
}
