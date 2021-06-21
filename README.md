# Statamic Jobs
![Statamic 3.1+](https://img.shields.io/badge/Statamic-3.1+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonassiewertsen/statamic-jobs.svg?style=for-the-badge)](https://packagist.org/packages/jonassiewertsen/statamic-jobs)

## Requirements
- PHP 8.0
- Laravel 8
- Statamic >= 3.1

# Support
I love to share with the community. Nevertheless, it does take a lot of work, time and effort. 

[Sponsor me on GitHub](https://github.com/sponsors/jonassiewertsen/) to support my work and the support for this addon.

# Setup
1. composer require `jonassiewertsen/statamic-jobs`
2. Set the queue
```php
// config/queue.php
'failed' => [
'driver' => env('QUEUE_FAILED_DRIVER', 'statamic'),
// collection stuff
],
```

# License 
This plugin is published under the MIT license. Feel free to use it and remember to spread love.

