<?php

namespace Jonassiewertsen\Jobs\Tests;

use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Support\Str;

trait PreventSavingStacheItemsToDisk
{
    protected string $fakeStacheDirectory = __DIR__.'/__fixtures__/';
    protected array $originStacheDirectories = [];

    protected function preventSavingStacheItemsToDisk()
    {
        $this->fakeStacheDirectory = Path::tidy($this->fakeStacheDirectory);

        $this->fakeStores()
             ->copyCollections();
    }

    protected function deleteFakeStacheDirectory(): self
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);

        mkdir($this->fakeStacheDirectory);
        touch($this->fakeStacheDirectory.'/.gitkeep');

        return $this;
    }

    /**
     * Faked stores will be saved in the fixtures folder, to avoid working with real data.
     */
    private function fakeStores(): self
    {
        Stache::stores()->each(function ($store) {
            $dir = Path::tidy('/__fixtures__');
            $relative = str_after(str_after($store->directory(), $dir), '/');
            $store->directory($this->fakeStacheDirectory.'/'.$relative);
        });

        return $this;
    }

    /**
     * To use exsisting data from for collections, we will copy those data into our
     * faked directories to test against them.
     */
    private function copyCollections(): self
    {
        app('files')->copyDirectory(
            Str::start(__DIR__.'/../resources/collections', '/'), // from
            Stache::store('collections')->directory()                    // to
        );

        return $this;
    }
}
