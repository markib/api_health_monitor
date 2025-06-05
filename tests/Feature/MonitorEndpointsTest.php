<?php

namespace Tests\Feature;

use App\Jobs\CheckEndpointStatus;
use App\Mail\EndpointDownNotification;
use App\Models\Client;
use App\Models\Endpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MonitorEndpointsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_sends_email_when_endpoint_is_down()
    {
        Mail::fake();

        // Fake HTTP response to simulate a down endpoint
        Http::fake([
            'http://fake-endpoint.test/fail' => Http::response(null, 500)
        ]);

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
          
            return $mail->endpoint->url === $endpoint->url
            && $mail->hasFrom('alerts@example.com');

        });

        // Refresh the endpoint and check status is updated to "down"
        $endpoint->refresh();
        $this->assertEquals('down', $endpoint->last_status);
    }

    #[Test]
    public function test_endpoint_checker_alerts_on_failure(): void
    {
        Mail::fake();
        Queue::fake();

        Http::fake([
            'http://fake-endpoint.test/fail*' => Http::response(null, 500)
        ]);

        $client = Client::factory()->create([
            'email' => 'client@example.com'
        ]);
        $endpoint = Endpoint::factory()->create([
            'client_id' => $client->id,
            'url' => 'http://fake-endpoint.test/fail',
            'last_status' => 'up',
            'is_active' => true
        ]);

        Log::info("Test setup: Endpoint ID={$endpoint->id}, URL={$endpoint->url}, Client={$client->email}, IsActive={$endpoint->is_active}");
        // $this->artisan('app:monitor-endpoints')->run();
        // CheckEndpointStatus::dispatch($endpoint);

        $job = new CheckEndpointStatus($endpoint);
        $job->handle();
        // Queue::assertPushed(CheckEndpointStatus::class, function ($job) use ($endpoint) {
        //     Log::info("Queue assertion: Job endpoint ID={$job->endpoint->id}, URL={$job->endpoint->url}");
        //     return $job->endpoint->id === $endpoint->id;
        //         // && $job->endpoint->url === 'http://fake-endpoint.test/fail';
        //         // && $job->endpoint->client->email === 'client@example.com';
        // });

        Mail::assertSent(EndpointDownNotification::class, function ($mail) use ($endpoint) {
            return $mail->endpoint->id === $endpoint->id
                && $mail->envelope()->subject === "{$endpoint->url} is unavailable!";
        });

        $endpoint->refresh();
        $this->assertEquals('down', $endpoint->last_status);
    }

    #[Test]
    public function test_endpoint_checker_passes_on_success(): void
    {
        // Fake the mailer to track queued emails
        Mail::fake();

        // Fake HTTP response to simulate a successful endpoint
        Http::fake([
            'http://fake-endpoint.test/success' => Http::response(['status' => 'ok'], 200)
        ]);

        // Create test data
        $client = Client::factory()->create([
            'email' => 'client@example.com'
        ]);
        $endpoint = Endpoint::factory()->create([
            'client_id' => $client->id,
            'url' => 'http://fake-endpoint.test/success',
            'last_status' => 'down'
        ]);

        // Run the artisan command
        $this->artisan('app:monitor-endpoints')->run();

        // Assert no email was queued
        Mail::assertNotQueued(EndpointDownNotification::class);

        // Assert the endpoint status is updated to "up"
        $endpoint->refresh();
        $this->assertEquals('up', $endpoint->last_status);
    }

    #[Test]
    public function test_command_dispatches_jobs()
    {
        Queue::fake();

        Endpoint::factory()->count(3)->create(['is_active' => true]);

        $this->artisan('app:monitor-endpoints')
            ->assertExitCode(0);

        Queue::assertCount(3);
    }

   
}
