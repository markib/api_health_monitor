<?php

namespace App\Console\Commands;

use App\Jobs\CheckEndpointStatus;
use Illuminate\Console\Command;
use App\Models\Endpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\EndpointDownNotification;
use Illuminate\Support\Facades\Log;

class MonitorEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitor-endpoints {--sync : Run jobs synchronously}';

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
        
        $sync = $this->option('sync');
        $chunkSize = 500; // Process in batches

        Log::info('Starting endpoint monitoring process.', [
            'mode' => $sync ? 'sync' : 'queue',
            'chunkSize' => $chunkSize
        ]);

        Endpoint::active()->chunk($chunkSize, function ($endpoints)  use ($sync) {
            Log::info('Processing new chunk', ['count' => $endpoints->count()]);

            $endpoints->each(function ($endpoint) use ($sync) {
                if ($sync) {
                    Log::info("Checking endpoint synchronously: {$endpoint->url}");
                    (new CheckEndpointStatus($endpoint))->handle(); // run inline
                } else {
                    Log::info("Dispatching job for endpoint: {$endpoint->url}");
                    CheckEndpointStatus::dispatch($endpoint)->onQueue('monitoring');
                }
            });
            if (! $sync) {
            sleep(1); // Add delay between chunks
            }
        });
        $this->info('Dispatched endpoint check jobs successfully.');
        Log::info('Endpoint monitoring process completed.');
    }

    protected function sendAlert(Endpoint $endpoint): void
    {
        try{
            Mail::to($endpoint->client->email)->send(
                new EndpointDownNotification($endpoint->url)
            );
        } catch (\Throwable $e) {
            Log::error("Failed to send alert for endpoint {$endpoint->url}: " . $e->getMessage());
        }
        
    }
}
