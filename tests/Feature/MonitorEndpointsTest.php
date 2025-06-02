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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_email_when_endpoint_is_down()
    {
        Mail::fake();

        // Create client and endpoint with initial status "up"
        $client = Client::factory()->create();

        $endpoint = Endpoint::factory()->create([
            'client_id' => $client->id,
            'url' => 'http://fake-endpoint.test/fail',
            'last_status' => 'up',
        ]);

        // Run the artisan command and execute immediately
        $this->artisan('app:monitor-endpoints')->run();

        // Assert the EndpointDownNotification mail was sent with the right endpoint
        Mail::assertSent(EndpointDownNotification::class, function ($mail) use ($endpoint) {
          
            return $mail->url === $endpoint->url;

        });

        // Refresh the endpoint and check status is updated to "down"
        $endpoint->refresh();
        $this->assertEquals('down', $endpoint->last_status);
    }
}
