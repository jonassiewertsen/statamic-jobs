<?php

namespace Jonassiewertsen\Jobs\Tests\Unit;

use Jonassiewertsen\Jobs\Queue\Failed\StatamicEntryFailedJobProvider;
use Jonassiewertsen\Jobs\Tests\TestCase;

class StatamicEntryBindingTest extends TestCase
{
    /** @test */
    public function without_any_config_the_default_failing_job_provider_will_be_loaded()
    {
        $this->assertInstanceOf(
            'Illuminate\Queue\Failed\DatabaseUuidFailedJobProvider',
            app()->make('queue.failer')
        );
    }

//    /** @test */
//    public function the_statamic_entry_failed_job_provider_can_be_loaded()
//    {
//        // TODO: Set the config before loading the service provider for testing.
//        config()->set('queue.failed.driver', 'statamic');
//
//        $this->assertInstanceOf(
//            StatamicEntryFailedJobProvider::class,
//            app()->make('queue.failer')
//        );
//    }
}
