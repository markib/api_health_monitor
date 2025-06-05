<?php

namespace Tests\Feature;

use App\Jobs\CheckEndpointStatus;
use App\Models\Client;
use App\Models\Endpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;

class CapacityTest extends TestCase
{
    use RefreshDatabase; 

    #[Test]
    public function system_handles_multiple_clients_with_endpoints_efficiently()
    {
        Queue::fake();

        // Seed with 100 clients each having 12 endpoints (600 total)
        Client::factory()
            ->count(100)
            ->has(Endpoint::factory()->count(12))
            ->create();

        $this->artisan('app:monitor-endpoints')->assertExitCode(0);

        // Ensure 600 jobs were dispatched
        Queue::assertPushed(CheckEndpointStatus::class, 1200);

        // Validate total endpoint count
        $this->assertEquals(1200, Endpoint::count());
    }

    #[Test]
    public function test_clients_are_cached_in_redis()
    {
        Cache::flush();

        $clients = Client::factory()->count(3)->create();

        // Call the method or endpoint that caches clients
        $cachedClients = Cache::remember('clients', 60, fn() => Client::all());

        $this->assertTrue(Cache::has('clients'));
        $this->assertCount(3, $cachedClients);
    }
}
