<?php

namespace Jonassiewertsen\Jobs\Tests\Unit;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Jonassiewertsen\Jobs\Queue\Failed\StatamicEntryFailedJobProvider;
use Jonassiewertsen\Jobs\Tests\TestCase;
use Statamic\Entries\Entry;

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

        $this->assertCount(1, Entry::all());
        $this->assertEquals(Entry::all()->first()->get('uuid'), $uuid);
        $this->assertEquals(Entry::all()->first()->get('failed_at'), $now);
        $this->assertEquals(Entry::all()->first()->get('exception'), $exception);
        $this->assertEquals(Entry::all()->first()->slug(), $now->format('Ymd_His').'_'.$uuid);
    }

    /** @test */
    public function it_can_retrieve_all_failed_jobs()
    {
        $entry = $this->createJobEntry([
            'uuid'      => (string) Str::uuid(),
            'failed_at' => time(),
        ]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertEquals(
            [[
                 'uuid'      => $entry->get('uuid'),
                 'failed_at' => $entry->get('failed_at'),
             ]],
            $provider->all()
        );
    }

    /** @test */
    public function a_Single_job_can_be_found()
    {
        $entry = $this->createJobEntry([
            'uuid'       => (string) Str::uuid(),
            'connection' => 'connection',
            'queue'      => 'queue',
        ]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertEquals(
            $entry->id(),
            $provider->find($entry->id())->id
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
        $entry = $this->createJobEntry([]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertCount(1, Entry::all());

        $this->assertTrue($provider->forget($entry->id()));
        $this->assertFalse($provider->forget('not-existing'));

        $this->assertCount(0, Entry::all());
    }

    /** @test */
    public function it_can_flush_all_jobs()
    {
        $this->createJobEntry([]);
        $this->createJobEntry([]);

        $provider = new StatamicEntryFailedJobProvider();

        $this->assertCount(2, Entry::all());

        $provider->flush();

        $this->assertCount(0, Entry::all());
    }

    private function createJobEntry(array $data): Entry
    {
        return tap(Entry::make()
            ->collection('failed_jobs')
            ->blueprint('failed_job')
            ->data($data))->save();
    }
}
