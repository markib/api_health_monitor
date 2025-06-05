<?php

namespace Tests\Feature;

use App\Models\Endpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Mail;


class ReliabilityTest extends TestCase
{
    
    #[Test]
    public function handles_mixed_success_and_failure_responses()
    {
        Http::fake([
            '*.test/success' => Http::response(),
            '*.test/fail' => Http::response(null, 500)
        ]);

        Endpoint::factory()
            ->count(4200)
            ->state(['url' => fake()->url() . '/success'])
            ->create();

        Endpoint::factory()
            ->count(1800)
            ->state(['url' => fake()->url() . '/fail'])
            ->create();

        Mail::fake();
        $this->artisan('monitor:endpoints');

        Mail::assertSentCount(1800);
    }
}
