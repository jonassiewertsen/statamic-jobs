<?php

namespace Jonassiewertsen\Jobs;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->bootFileFailedJobProvider();
    }

    private function bootFileFailedJobProvider(): self
    {
        if (config('queue.failed.driver') === 'file') {
            $this->app->singleton('queue.failer', function ($app) {
                return new FileFailedJobProvider($app['config']['queue.failed']);
            });
        }

        return $this;
    }
}
