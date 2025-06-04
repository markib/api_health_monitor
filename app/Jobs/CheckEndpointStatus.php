<?php

namespace App\Jobs;

use App\Mail\EndpointDownNotification;
use App\Models\Endpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckEndpointStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Endpoint $endpoint
    ) {
        Log::info("Constructing CheckEndpointStatus: ID={$endpoint->id}, URL={$endpoint->url}, ClientEmail=" . ($endpoint->client ? $endpoint->client->email : 'null'));
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        Log::info("Handling CheckEndpointStatus for endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}");
        try {
            Log::info("Making HTTP request to: {$this->endpoint->url}");
            $response = Http::timeout(8)->get($this->endpoint->url);
            $isUp = $response->successful();
            Log::info("Checked endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}, Status={$response->status()}, isUp=" . ($isUp ? 'true' : 'false'));

            if (!$isUp) {
                Log::info("Endpoint down, sending alert for: ID={$this->endpoint->id}");
                $this->sendAlert();
                $this->updateStatus('down');
            } else {
                Log::info("Endpoint up, updating status for: ID={$this->endpoint->id}");
                $this->updateStatus('up');
            }
        } catch (\Throwable $e) {
            Log::error("Exception for endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}, Error={$e->getMessage()}");
            $this->sendAlert();
            $this->updateStatus('down');
        }
    }

    protected function sendAlert(): void
    {
        try {
            $email = $this->endpoint->client->email;
            Log::info("Sending EndpointDownNotification for endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}, To={$email}");
            Mail::to($email)->send(new EndpointDownNotification($this->endpoint));
            // Alternative: Use queue() to match test expectation
            // Mail::to($email)->queue(new EndpointDownNotification($this->endpoint));
            Log::info("Successfully sent EndpointDownNotification for endpoint: ID={$this->endpoint->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to send EndpointDownNotification for endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}, Error={$e->getMessage()}");
            throw $e;
        }
    }

    protected function updateStatus(string $status): void
    {
        Log::info("Updating status for endpoint: ID={$this->endpoint->id}, Status={$status}");
        $this->endpoint->last_status = $status;
        $this->endpoint->save();
    }
}
