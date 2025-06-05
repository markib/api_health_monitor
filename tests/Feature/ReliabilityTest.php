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
    use RefreshDatabase;
    #[Test]
    public function handles_mixed_success_and_failure_responses()
    {
        Http::fake([
            'https://api.test/success*' => Http::response(),
            'https://api.test/fail*' => Http::response(null, 500),
        ]);

        Endpoint::factory()
            ->count(20)
            ->state(['url' => 'https://api.test/success'])
            ->create();

        Endpoint::factory()
            ->count(8)
            ->state(['url' => 'https://api.test/fail'])
            ->create();

        Mail::fake();
        $this->artisan('app:monitor-endpoints --sync');

        Mail::assertSentCount(8);
    }
}
