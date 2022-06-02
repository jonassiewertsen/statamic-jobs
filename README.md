<!-- statamic:hide -->
# Statamic Jobs
![Statamic 3.1+](https://img.shields.io/badge/Statamic-3.1+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonassiewertsen/statamic-jobs.svg?style=for-the-badge)](https://packagist.org/packages/jonassiewertsen/statamic-jobs)
<!-- /statamic:hide -->

Laravel does handle failed jobs by default, but does need a database. What if your Statamic setup does not have or need a database?

Well ... failing jobs can not be handled!

This addon does provide a simple solution for small Statamic setups:
A failing job will be saved as flat file in the `storage`. 

## Installation

### 1. Require the package
```bash
composer require jonassiewertsen/statamic-jobs
```

### 2. Configure the `Failed Queue Jobs` Driver
```php
// config/queue.php

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'file'),
        // 'storage_path' => storage_path('failed-jobs'), 
    ],
    
    // INSTEAD of fx
    // 'failed' => [
    //     'driver' => env('QUEUE_FAILED_DRIVER', 'statamic'),
    //     'database' => env('DB_CONNECTION', 'mysql'),
    //     'table' => 'failed_jobs',
    // ],
```

## Usage
You can access your failed jobs via the default artisan commands. Fx.:

`php artisan queue:failed` <- list all failed jobs

`php artisan queue:retry JOB_UUID_ID` <- Retry a given job

`php artisan queue:flush` <- Flush all failed jobs

## Requirements
- PHP 8.0
- Laravel 8
- Statamic >= 3.1

## Support
I love to share with the community. Nevertheless, it does take a lot of work, time and effort. 

[Sponsor me on GitHub](https://github.com/sponsors/jonassiewertsen/) to support my work and the support for this addon.

## License 
This plugin is published under the MIT license. Feel free to use it and remember to spread love.

