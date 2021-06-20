<?php

namespace Jonassiewertsen\Jobs;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->mergeConfigFrom(__DIR__.'/../config/jobs.php', 'statamic.jobs');

        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__.'/../config/jobs.php' => config_path('statamic/jobs.php'),
            ], 'jobs-config');

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
