<?php

namespace App\Jobs;

use App\Mail\EndpointDownNotification;
use App\Models\Endpoint;
use App\Models\MonitoringResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CheckEndpointStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Endpoint $endpoint
    ) {
        Log::info("Constructing CheckEndpointStatus: ID={$endpoint->id}, URL={$endpoint->url}, ClientEmail=" . ($endpoint->client ? $endpoint->client->email : 'null'));
        $this->onQueue('monitoring');
    }

    public function handle(): void
    {
        Log::info("Handling CheckEndpointStatus for endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}");
        $start = microtime(true);
        try {
            Log::info("Making HTTP request to: {$this->endpoint->url}");
            $response = Http::timeout(8)->get($this->endpoint->url);
            $statusCode = $response->getStatusCode();
            $responseTime = (microtime(true) - $start) * 1000;
            $isUp = $response->successful();
            $isHealthy = $statusCode >= 200 && $statusCode < 300;
            Log::info("Checked endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}, Status={$response->status()}, isUp=" . ($isUp ? 'true' : 'false'));

            $result = MonitoringResult::create([
                'endpoint_id' => $this->endpoint->id,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTime,
                'is_healthy' => $isHealthy,
                'checked_at' => now(),
            ]);
            
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
            $result = MonitoringResult::create([
                'endpoint_id' => $this->endpoint->id,
                'is_healthy' => false,
                'error_message' => $e->getMessage(),
                'checked_at' => now(),
            ]);
            $this->sendAlert();
            $this->updateStatus('down');
        }
    }

    protected function sendAlert(): void
    {
        try {
            $email = $this->endpoint->client->email;
            Log::info("Sending EndpointDownNotification for endpoint: ID={$this->endpoint->id}, URL={$this->endpoint->url}, To={$email}");
            $key = 'email:' . $this->endpoint->client->email . ':' . $this->endpoint->id;
            if (RateLimiter::tooManyAttempts($key, 1)) {
                return;
            }
            RateLimiter::hit($key, 3600);
            // Mail::to($this->endpoint->client->email)->queue(new EndpointFailure($this->endpoint, $this->endpoint->latestResult));
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
