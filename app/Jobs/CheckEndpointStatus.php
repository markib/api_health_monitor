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

class CheckEndpointStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Endpoint $endpoint;

    /**
     * Create a new job instance.
     */
    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $response = Http::timeout(8)->get($this->endpoint->url);

            if (!$response->successful()) {
                $this->sendAlert();
                $this->updateStatus('down');
            } else {
                $this->updateStatus('up');
            }
        } catch (\Throwable $e) {
            // Timeout or other error
            $this->sendAlert();
            $this->updateStatus('down');
        }
    }

    protected function sendAlert(): void
    {
        Mail::to($this->endpoint->client->email)->send(
            new EndpointDownNotification($this->endpoint)
        );
    }

    protected function updateStatus(string $status): void
    {
        $this->endpoint->last_status = $status;
        $this->endpoint->save();
    }
}
