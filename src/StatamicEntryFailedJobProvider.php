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
     * Failed Jobs will be saved in this directory.
     */
    protected string $storagePath;

    /**
     * Create a new statamic entry failed job provider.
     */
    public function __construct(array $config = [])
    {
        $this->storagePath = $config['storage_path'] ?? storage_path('failed-jobs');
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
        $id = json_decode($payload, true)['uuid'];
        $now = Date::now();

        $job = [
            'id'         => $id,
            'connection' => $connection,
            'queue'      => $queue,
            'payload'    => $payload,
            'exception'  => (string) $exception,
            'failed_at'  => $now,
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
            return $this->parseFile($fileName);
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
        $fileName = $this->getFileName($id);

        if (is_null($fileName)) {
            return null;
        }

        return (object) $this->parseFile($fileName);
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

    private function parseFile(string $fileName)
    {
        $file = YAML::parse(File::get($fileName));
        $file['failed_at'] = isset($file['failed_at']) ? Carbon::parse($file['failed_at']) : null;

        return $file;
    }
}
