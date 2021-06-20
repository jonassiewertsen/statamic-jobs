<?php

namespace Jonassiewertsen\Jobs\Queue\Failed;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Statamic\Facades\Entry;

class StatamicEntryFailedJobProvider implements FailedJobProviderInterface
{
    /**
     * Create a new statamic entry failed job provider.
     */
    public function __construct()
    {
        //
    }

    /**
     * Log a failed job into storage.
     *
     * @param  string  $connection
     * @param  string  $queue
     * @param  string  $payload
     * @param  \Throwable  $exception
     * @return int|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $exception = (string) $exception;
        $uuid = json_decode($payload, true)['uuid'];
        $now = Date::now();

        $job = tap(Entry::make()
            ->collection('failed_jobs')
            ->blueprint('failed_job')
            ->slug($this->slug($uuid, $now))
            ->data([
                'uuid' => $uuid,
                'connection' => $connection,
                'queue' => $queue,
                'payload' => $payload,
                'exception' => $exception,
                'failed_at' => $now,
            ]))->save();

        return $job->id();
    }

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        //
    }

    /**
     * Get a single failed job.
     *
     * @param  mixed  $id
     * @return object|null
     */
    public function find($id)
    {
        //
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function forget($id)
    {
        //
    }

    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    public function flush()
    {
        //
    }

    private function slug(string $uuid, Carbon $now): string
    {
        $time = $now->format('Ymd_His');

        return "{$time}_{$uuid}";
    }
}
