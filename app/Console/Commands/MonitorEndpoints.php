<?php

namespace App\Console\Commands;

use App\Jobs\CheckEndpointStatus;
use Illuminate\Console\Command;
use App\Models\Endpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\EndpointDownNotification;

class MonitorEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitor-endpoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor API endpoints and send alerts if any are down.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoints = Endpoint::with('client')->where('is_active', true)->get();

        foreach ($endpoints as $endpoint) {
            // try {
            //     $response = Http::timeout(8)->get($endpoint->url);

            //     if (!$response->successful()) {
            //         $this->sendAlert($endpoint);
            //     }
            // } catch (\Throwable $e) {
            //     $this->sendAlert($endpoint);
            // }
            CheckEndpointStatus::dispatch($endpoint);
        }
        $this->info('Dispatched endpoint check jobs for ' . $endpoints->count() . ' endpoints.');
    }

    protected function sendAlert(Endpoint $endpoint): void
    {
        Mail::to($endpoint->client->email)->send(
            new EndpointDownNotification($endpoint->url)
        );
    }
}
