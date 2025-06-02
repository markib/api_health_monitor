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
        Client::all()->each(function ($client) {
            Endpoint::factory()
                ->count(3)
                ->create([
                    'client_id' => $client->id,
                ]);
        });
    }
}
