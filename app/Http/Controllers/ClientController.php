<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClientController extends Controller
{
    // Return all clients (only emails and ids)
    public function index()
    {
        $clients = Cache::remember('clients_with_endpoints', 60, function () {
            return Client::with(['endpoints' => function ($query) {
                $query->limit(12);
            }])->get();
        });

        return response()->json($clients);
    }

    // Return endpoints for one client
    public function show(Client $client)
    {
        $cacheKey = 'client_' . $client->id . '_endpoints';

        $endpoints = Cache::remember($cacheKey, 60, function () use ($client) {
            return $client->endpoints()->select('id', 'url')->get();
        });

        return response()->json($endpoints);
    }
}
