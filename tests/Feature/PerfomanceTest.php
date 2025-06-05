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
    #[Test]
    public function processes_6000_endpoints_under_5_minutes()
    {
        Endpoint::factory()
            ->count(6000)
            ->create();

        $start = microtime(true);
        $this->artisan('app:monitor-endpoints');
        $duration = microtime(true) - $start;

        $this->assertLessThan(300, $duration);
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