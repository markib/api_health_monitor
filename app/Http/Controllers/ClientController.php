<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    // Return all clients (only emails and ids)
    public function index()
    {
        try {
            $clients = Cache::remember('clients_with_endpoints', 60, function () {
                return Client::with(['endpoints' => function ($query) {
                    $query->limit(12);
                }])->get();
            });

            return response()->json($clients, 200);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch clients:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to load clients.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Return endpoints for one client
    public function show(Client $client)
    {
        try {
            $cacheKey = 'client_' . $client->id . '_endpoints';

            $endpoints = Cache::remember($cacheKey, 60, function () use ($client) {
                return $client->endpoints()->select('id', 'url')->get();
            });

            return response()->json($endpoints, 200);
        } catch (\Throwable $e) {
            Log::error("Failed to fetch endpoints for client ID {$client->id}", [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Failed to load endpoints for this client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
