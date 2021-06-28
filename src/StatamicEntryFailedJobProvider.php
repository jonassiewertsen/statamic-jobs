<?php

namespace Jonassiewertsen\Jobs;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

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
        $uuid = json_decode($payload, true)['uuid'];
        $now = Date::now();

        $job = [
            'id'         => $id = $uuid,
            'uuid'         => $id,
            'connection' => $connection,
            'queue'      => $queue,
            'payload'    => $payload,
            'exception'  => (string) $exception,
            'failed_at'  => $now->toIso8601String(),
        ];

        File::put($this->createFileName($id, $now), YAML::dump($job));

        return $id;
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
     * @param mixed $id
     * @return object|null
     */
    public function find($id)
    {
        $file = File::get($this->getFileName($id));

        if (is_null($file)) {
            return null;
        }

        return (object) YAML::parse($file);
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param mixed $id
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
        File::delete($this->storagePath);
    }

    /**
     * @param string $id
     * @return string|null
     */
    private function getFileName(string $id): string | null
    {
        return File::getFiles($this->storagePath)
            ->filter(fn ($fileName) => str_contains($fileName, $id))
            ->first();
    }

    /**
     * @param string $id
     * @param Carbon $now
     * @return string
     */
    private function createFileName(string $id, Carbon $now): string
    {
        return Str::finish($this->storagePath, '/').$this->slug($id, $now).'.yaml';
    }

    /**
     * The slug and thereby the filename will be a combination from the
     * date and the Jobs UUID to always be unique.
     *
     * @param string $id
     * @param Carbon $now
     * @return string
     */
    private function slug(string $id, Carbon $now): string
    {
        $time = $now->format('Ymd_His');

        return "{$time}_{$id}";
    }
}
