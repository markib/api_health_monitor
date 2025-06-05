<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Endpoint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EndpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If no clients exist, create one
        if (Client::count() === 0) {
            Client::factory()->create();
        }

        Client::all()->each(function ($client) {
            // 2 working endpoints (200 OK)
            Endpoint::factory()->createMany([
                ['client_id' => $client->id, 'url' => 'https://httpstat.us/200'],
                ['client_id' => $client->id, 'url' => 'https://httpstat.us/204'],
            ]);

            // 2 failing endpoints (500 & 404)
            Endpoint::factory()->createMany([
                ['client_id' => $client->id, 'url' => 'https://httpstat.us/500'],
                ['client_id' => $client->id, 'url' => 'https://httpstat.us/404'],
            ]);

            // 1 timeout endpoint (non-routable IP to simulate timeout)
            Endpoint::factory()->create([
                'client_id' => $client->id,
                'url' => 'http://10.255.255.1', // simulates timeout
            ]);
        });
    }
}
