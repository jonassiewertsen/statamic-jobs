<?php

namespace Jonassiewertsen\Jobs\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

class TestCase extends OrchestraTestCase
{
    use WithFaker;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        require_once __DIR__.'/Kernel.php';

        parent::setUp();

        $this->preventSavingStacheItemsToDisk();
        Blueprint::setDirectory(__DIR__.'/../resources/blueprints');

        \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.1.0-testing');
    }

    public function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();

        parent::tearDown();
    }

    /**
     * Load package service provider.
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Statamic\Providers\StatamicServiceProvider::class,
            \Jonassiewertsen\Jobs\ServiceProvider::class,
        ];
    }

    /**
     * Load package alias.
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    /**
     * Load Environment.
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'jonassiewertsen/statamic-jobs' => [
                'id'        => 'jonassiewertsen/statamic-jobs',
                'namespace' => 'Jonassiewertsen\\Jobs\\',
            ],
        ];
    }

    /**
     * Resolve the Application Configuration and set the Statamic configuration.
     * @param \Illuminate\Foundation\Application $app
     */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'routes', 'static_caching', 'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../vendor/statamic/cms/config/{$config}.php"));
        }

        // Setting the user repository to the default flat file system
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.watcher', false);
        $app['config']->set('statamic.stache.stores.users', [
            'class'     => UsersStore::class,
            'directory' => __DIR__.'/__fixtures/users',
        ]);
    }
}
