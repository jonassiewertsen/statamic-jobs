<?php

namespace Jonassiewertsen\Jobs\Queue\Failed;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class  StatamicEntryFailedJobProvider implements FailedJobProviderInterface
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
     * Failed Jobs will be saved in this directory.
     */
    protected string $storagePath;

    /**
     * Create a new statamic entry failed job provider.
     */
    public function __construct(array $config = [])
    {
        $this->storagePath = $config['storagePath'] ?? storage_path('failed-jobs/');
        $this->collectionName = $config['collection'] ?? 'failed_jobs';
        $this->blueprintName = $config['blueprint'] ?? 'failed_job';
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

        $job = Entry::make()
            ->collection($this->collectionName)
            ->blueprint($this->blueprintName)
            ->slug($this->slug($uuid, $now))
            ->data([
                'uuid' => $uuid,
                'connection' => $connection,
                'queue' => $queue,
                'payload' => $payload,
                'exception' => $exception,
                'failed_at' => $now->toIso8601String(),
            ]);

        $absoluteFilePath = Str::finish($this->storagePath, '/').$this->slug($uuid, $now).'.yaml';

        File::put($absoluteFilePath, YAML::dump($job->data()->all()));

        return $job->id();
    }

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        return File::getFiles($this->storagePath)->map(function ($fileName) {
            return YAML::parse(File::get($fileName));
        })->all();
    }

    /**
     * Get a single failed job.
     *
     * @param  mixed  $id
     * @return object|null
     */
    public function find($id)
    {
        $job = (object) YAML::parse(File::get($this->getFileName($id)));

        if (is_null($job)) {
            return null;
        }

        return $job;
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function forget($id)
    {
        $filename = $this->getFileName($id);

        if (is_null($filename)) {
            return false;
        }

        return File::delete($filename);
    }

    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    public function flush()
    {
        File::cleanDirectory($this->storagePath);
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

    private function getFileName(string $uuid): string | null
    {
        return File::getFiles($this->storagePath)->filter(function ($fileName) use ($uuid) {
            return str_contains($fileName, $uuid);
        })->first();
    }
}
