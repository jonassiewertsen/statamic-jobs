<?php

namespace Jonassiewertsen\Jobs;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->bootStatamicFailedJobProvider();
    }

    private function bootStatamicFailedJobProvider(): self
    {
        if (config('queue.failed.driver') === 'statamic') {
            $this->app->singleton('queue.failer', function ($app) {
                return new StatamicEntryFailedJobProvider($app['config']['queue.failed']);
            });
        }

        return $this;
    }
}
