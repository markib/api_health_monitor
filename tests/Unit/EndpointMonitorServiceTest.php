<?php

namespace Tests\Unit;

use App\Jobs\CheckEndpointStatus;
use App\Mail\EndpointDownNotification;
use App\Models\Client;
use App\Models\Endpoint;
use App\Services\EndpointMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EndpointMonitorServiceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_it_dispatches_job_for_valid_endpoint()
    {
        Queue::fake();

        $client = Client::factory()->create();
        $endpoint = Endpoint::factory()->create(['client_id' => $client->id]);

        $service = new EndpointMonitorService($endpoint);
        $service->dispatchMonitoringJobs();

        Queue::assertPushed(CheckEndpointStatus::class, function ($job) use ($endpoint) {
            return $job->endpoint->id === $endpoint->id;
        });
    }

    public function test_it_sends_email_when_endpoint_fails()
    {
        Mail::fake();
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $client = Client::factory()->create();
        $endpoint = Endpoint::factory()->create(['client_id' => $client->id]);

        $service = new EndpointMonitorService($endpoint);
        $service->checkAndAlert($endpoint);

        Mail::assertSent(EndpointDownNotification::class, function ($mail) use ($endpoint) {
            return $mail->endpoint->url === $endpoint->url;
        });
    }

    public function test_it_does_not_send_email_on_success()
    {
        Mail::fake();
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $client = Client::factory()->create();
        $endpoint = Endpoint::factory()->create(['client_id' => $client->id]);

        $service = new EndpointMonitorService($endpoint);
        $service->checkAndAlert($endpoint);

        Mail::assertNothingSent();
    }
}
