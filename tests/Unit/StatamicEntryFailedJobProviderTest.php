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

//    public function testCanRetrieveAllFailedJobs()
//    {
//        $dynamoDbClient = m::mock(DynamoDbClient::class);
//
//        $time = time();
//
//        $dynamoDbClient->shouldReceive('query')->once()->with([
//            'TableName' => 'table',
//            'Select' => 'ALL_ATTRIBUTES',
//            'KeyConditionExpression' => 'application = :application',
//            'ExpressionAttributeValues' => [
//                ':application' => ['S' => 'application'],
//            ],
//            'ScanIndexForward' => false,
//        ])->andReturn([
//            'Items' => [
//                [
//                    'application' => ['S' => 'application'],
//                    'uuid' => ['S' => 'uuid'],
//                    'connection' => ['S' => 'connection'],
//                    'queue' => ['S' => 'queue'],
//                    'payload' => ['S' => 'payload'],
//                    'exception' => ['S' => 'exception'],
//                    'failed_at' => ['N' => (string) $time],
//                    'expires_at' => ['N' => (string) $time],
//                ],
//            ],
//        ]);
//
//        $provider = new DynamoDbFailedJobProvider($dynamoDbClient, 'application', 'table');
//
//        $response = $provider->all();
//
//        $this->assertEquals([
//            (object) [
//                'id' => 'uuid',
//                'connection' => 'connection',
//                'queue' => 'queue',
//                'payload' => 'payload',
//                'exception' => 'exception',
//                'failed_at' => Carbon::createFromTimestamp($time)->format(DateTimeInterface::ISO8601),
//            ],
//        ], $response);
//    }
//
//    public function testASingleJobCanBeFound()
//    {
//        $dynamoDbClient = m::mock(DynamoDbClient::class);
//
//        $time = time();
//
//        $dynamoDbClient->shouldReceive('getItem')->once()->with([
//            'TableName' => 'table',
//            'Key' => [
//                'application' => ['S' => 'application'],
//                'uuid' => ['S' => 'id'],
//            ],
//        ])->andReturn([
//            'Item' => [
//                'application' => ['S' => 'application'],
//                'uuid' => ['S' => 'uuid'],
//                'connection' => ['S' => 'connection'],
//                'queue' => ['S' => 'queue'],
//                'payload' => ['S' => 'payload'],
//                'exception' => ['S' => 'exception'],
//                'failed_at' => ['N' => (string) $time],
//                'expires_at' => ['N' => (string) $time],
//            ],
//        ]);
//
//        $provider = new DynamoDbFailedJobProvider($dynamoDbClient, 'application', 'table');
//
//        $response = $provider->find('id');
//
//        $this->assertEquals(
//            (object) [
//                'id' => 'uuid',
//                'connection' => 'connection',
//                'queue' => 'queue',
//                'payload' => 'payload',
//                'exception' => 'exception',
//                'failed_at' => Carbon::createFromTimestamp($time)->format(DateTimeInterface::ISO8601),
//            ], $response
//        );
//    }
//
//    public function testNullIsReturnedIfJobNotFound()
//    {
//        $dynamoDbClient = m::mock(DynamoDbClient::class);
//
//        $dynamoDbClient->shouldReceive('getItem')->once()->with([
//            'TableName' => 'table',
//            'Key' => [
//                'application' => ['S' => 'application'],
//                'uuid' => ['S' => 'id'],
//            ],
//        ])->andReturn([]);
//
//        $provider = new DynamoDbFailedJobProvider($dynamoDbClient, 'application', 'table');
//
//        $response = $provider->find('id');
//
//        $this->assertNull($response);
//    }
//
//    public function testJobsCanBeDeleted()
//    {
//        $dynamoDbClient = m::mock(DynamoDbClient::class);
//
//        $dynamoDbClient->shouldReceive('deleteItem')->once()->with([
//            'TableName' => 'table',
//            'Key' => [
//                'application' => ['S' => 'application'],
//                'uuid' => ['S' => 'id'],
//            ],
//        ])->andReturn([]);
//
//        $provider = new DynamoDbFailedJobProvider($dynamoDbClient, 'application', 'table');
//
//        $provider->forget('id');
//    }
}
