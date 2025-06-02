<?php

namespace Tests\Feature;

use App\Mail\EndpointDownNotification;
use App\Models\Client;
use App\Models\Endpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MonitorEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_email_when_endpoint_is_down()
    {
        Mail::fake();

        // Create a client and a failing endpoint
        $client = Client::factory()->create();
        $endpoint = Endpoint::factory()->create([
            'client_id' => $client->id,
            'url' => 'http://fake-endpoint.test/fail', // non-existent
            'last_status' => 'up',
        ]);

        // Run the monitor command
        $this->artisan('app:monitor-endpoints');

        // Assert mail was sent
        Mail::assertSent(EndpointDownNotification::class, function ($mail) use ($endpoint) {
            return $mail->endpoint->is($endpoint);
        });

        $endpoint->refresh();
        $this->assertEquals('down', $endpoint->last_status);
    }
}
