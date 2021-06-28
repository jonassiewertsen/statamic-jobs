<?php

namespace Jonassiewertsen\Jobs\Tests\Unit;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Jonassiewertsen\Jobs\StatamicEntryFailedJobProvider;
use Jonassiewertsen\Jobs\Tests\TestCase;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\FileCollection;

class StatamicEntryFailedJobProviderTest extends TestCase
{
    /** @test */
    public function a_failed_job_will_be_properly_logged()
    {
        $uuid = (string) Str::uuid();

        Carbon::setTestNow($now = CarbonImmutable::now());

        $exception = new Exception('Something went wrong.');
        $provider = new StatamicEntryFailedJobProvider();

        $provider->log('connection', 'queue', json_encode(compact('uuid')), $exception);

        $this->assertCount(1, $this->allJobFiles());

        $jobFileName = $this->allJobFiles()->first();
        $this->assertTrue(str_contains($jobFileName, $now->format('Ymd_His').'_'.$uuid.'.yaml'));

        $job = (object) YAML::parse(File::get($jobFileName));

        $this->assertEquals($job->uuid, $uuid);
        $this->assertEquals($job->failed_at, $now->toIso8601String());
        $this->assertEquals($job->exception, (string) $exception);
    }

    /** @test */
    public function it_can_retrieve_all_failed_jobs()
    {
        $job = $this->createJobEntry([
            'uuid'      => (string) Str::uuid(),
            'failed_at' => time(),
        ]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertEquals(
            [[
                 'uuid'      => $job->uuid,
                 'failed_at' => $job->failed_at,
             ]],
            $provider->all()
        );
    }

    /** @test */
    public function a_Single_job_can_be_found()
    {
        $job = $this->createJobEntry([
            'uuid'       => (string) Str::uuid(),
            'connection' => 'connection',
            'queue'      => 'queue',
        ]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertEquals(
            $job->uuid,
            $provider->find($job->uuid)->uuid
        );
    }

    /** @test */
    public function returns_null_if_the_job_cant_be_found()
    {
        $provider = new StatamicEntryFailedJobProvider();

        $this->assertNull($provider->find('not-existing'));
    }

    /** @test */
    public function it_can_forget_a_job()
    {
        $job = $this->createJobEntry(['id' => 1]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertCount(1, $this->allJobFiles());

        $this->assertTrue($provider->forget($job->id));
        $this->assertFalse($provider->forget('not-existing'));

        $this->assertCount(0, $this->allJobFiles());
    }

    /** @test */
    public function it_can_flush_all_jobs()
    {
        $this->createJobEntry([]);
        $this->createJobEntry([]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertCount(2, $this->allJobFiles());

        $provider->flush();

        $this->assertCount(0, $this->allJobFiles());
    }

    private function createJobEntry(array $data): object
    {
        $uuid = $data['uuid'] ?? Str::uuid();
        $time = now()->format('Ymd_His');
        $fileName = "{$time}_{$uuid}";
        $absoluteFilePath = storage_path('failed-jobs/').$fileName.'.yaml';

        File::put($absoluteFilePath, YAML::dump($data));

        return (object) YAML::parse(File::get($absoluteFilePath));
    }

    private function allJobFiles(): FileCollection
    {
        return File::getFiles(storage_path('failed-jobs/'));
    }
}
