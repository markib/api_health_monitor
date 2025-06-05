<?php

namespace App\Services;

use App\Jobs\CheckEndpointStatus;
use App\Mail\EndpointDownNotification;
use App\Models\Endpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class EndpointMonitorService
{
    /**
     * The endpoint instance.
     *
     * @var \App\Models\Endpoint
     */
    public Endpoint $endpoint;
    /**
     * Create a new class instance.
     */
    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Dispatch queue jobs for all active endpoints.
     */
    public function dispatchMonitoringJobs(): void
    {
        Endpoint::with('client')
            ->where('is_active', true)
            ->chunk(100, function ($endpoints) {
                foreach ($endpoints as $endpoint) {
                    CheckEndpointStatus::dispatch($endpoint);
                }
            });
    }

    /**
     * Check endpoint and send email if down.
     */
    public function checkAndAlert(Endpoint $endpoint): void
    {
        try {
            $response = Http::timeout(8)->get($endpoint->url);

            if (!$response->successful()) {
                $this->notifyClient($endpoint);
            }
        } catch (\Throwable $e) {
            $this->notifyClient($endpoint);
        }
    }

    /**
     * Send alert email to client.
     */
    protected function notifyClient(Endpoint $endpoint): void
    {
        if ($endpoint->client && $endpoint->client->email) {
            Mail::to($endpoint->client->email)->send(
                new EndpointDownNotification($endpoint)
            );
        }
    }
}
