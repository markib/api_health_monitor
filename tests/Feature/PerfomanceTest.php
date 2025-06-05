<?php

namespace Tests\Feature;

use App\Models\Endpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\{Http, Queue};

class PerfomanceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function processes_6000_endpoints_under_5_minutes()
    {
        // Fake all HTTP requests to return a quick 200 OK response
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        Endpoint::factory()->count(6000)->create();

        $start = microtime(true);
        $this->artisan('app:monitor-endpoints');
        $duration = microtime(true) - $start;

        $this->assertLessThan(300, $duration); // 5 minutes
    }


    #[Test]
    public function processes_6000_endpoints_in_under_5_minutes()
    {
        $start = microtime(true);
        $this->artisan('app:monitor-endpoints');
        $duration = microtime(true) - $start;

        $this->assertLessThan(300, $duration);
    }
}