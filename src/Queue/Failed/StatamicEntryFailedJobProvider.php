<?php

namespace Jonassiewertsen\Jobs\Queue\Failed;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Statamic\Facades\Entry;

class StatamicEntryFailedJobProvider implements FailedJobProviderInterface
{
    /**
     * The Statamic collection where jobs will be saved.
     */
    protected string $collectionName;

    /**
     * The Statamic blueprint as structure for the entries.
     */
    protected string $blueprintName;

    /**
     * Create a new statamic entry failed job provider.
     */
    public function __construct()
    {
        $this->collectionName = config('statamic.jobs.collection', 'failed_jobs');
        $this->blueprintName = config('statamic.jobs.blueprint', 'failed_job');
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
            ->collection($this->collectionName)
            ->blueprint($this->blueprintName)
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
        return Entry::whereCollection($this->collectionName)
            ->map(fn ($entry) => $entry->data()->toArray())
            ->all();
    }

    /**
     * Get a single failed job.
     *
     * @param  mixed  $id
     * @return object|null
     */
    public function find($id)
    {
        $entry = Entry::find($id);

        if (is_null($entry)) {
            return null;
        }

        return (object) [
            'id' => $entry->id(),
            'slug' => $entry->slug(),
            'uuid' => $entry->get('uuid'),
            'connection' => $entry->get('connection'),
            'queue' => $entry->get('queue'),
            'payload' => $entry->get('payload'),
            'exception' => $entry->get('exception'),
            'failed_at' => $entry->get('failed_at'),
        ];
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function forget($id)
    {
        $entry = Entry::find($id);

        if (is_null($entry)) {
            return false;
        }

        return $entry->delete();
    }

    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    public function flush()
    {
        Entry::whereCollection($this->collectionName)
            ->each
            ->delete();
    }

    /**
     * The entry slug is automatically the file name of Statamic entries.
     * The slug and thereby the filename will be a combination of the
     * date and the Jobs UUID to always be unique.
     *
     * @param string $uuid
     * @param Carbon $now
     *
     * @return string
     */
    private function slug(string $uuid, Carbon $now): string
    {
        $time = $now->format('Ymd_His');

        return "{$time}_{$uuid}";
    }
}
