<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class ClientController extends Controller
{
    // Return all clients (only emails and ids)
    public function index()
    {
        try {
            

            $clients = Cache::store('redis')->remember('clients_with_endpoints', 60, function () {
                return Client::with(['endpoints' => function ($query) {
                    $query->limit(12);
                }])->orderBy('email', 'asc')->get();
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

    /**
     * Store a newly created client and their associated endpoints in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        
        // Validate the incoming request data
        // 'email' must be required, a valid email format, and unique in the 'clients' table
        // 'endpoints' must be required, an array, and contain at least one item
        // 'endpoints.*' ensures that each item within the 'endpoints' array is a valid URL
        $request->validate([
            'email' => ['required', 'email', 'unique:clients,email'],
            'endpoints' => ['required', 'array', 'min:1'],
            'endpoints.*' => ['required', 'url'],
        ]);
        
        try {
            
            // Create a new Client record in the database with the provided email
            $client = Client::create(['email' => $request->email]);
            
            // Iterate over the array of endpoints provided in the request
            // and create an Endpoint record for each, associating it with the newly created client
            foreach ($request->endpoints as $url) {
                $client->endpoints()->create(['url' => $url]);
            }
            
            // Invalidate the Redis cache for 'clients_with_endpoints'.
            // This is crucial because ClientController@index uses this cache,
            // and invalidating it ensures that the next time clients are fetched,
            // the new client and their endpoints will be included in the results.
            Cache::store('redis')->forget('clients_with_endpoints');

            // Return a JSON response indicating success, along with a message and the new client's ID.
            // HTTP status code 201 (Created) is appropriate for successful resource creation.
            return response()->json([
                'message' => 'Client and endpoints saved successfully!',
                'client_id' => $client->id
            ], 200);
        } catch (Throwable $e) {
            // Catch any unexpected exceptions that occur during the process.
            // Log the error for debugging purposes, including the error message and the request data.
            Log::error('Failed to create client or endpoints:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            // Return a JSON response with an error message and HTTP status code 500 (Internal Server Error).
            return response()->json([
                'message' => 'Failed to save client and endpoints.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Return endpoints for one client
    public function show(Client $client)
    {
        try {
            $cacheKey = 'client_' . $client->id . '_endpoints';
            
            $endpoints = Cache::store('redis')->remember($cacheKey, 60, function () use ($client) {
                return $client->endpoints()->select('id', 'url','client_id')->with(['latestResult:id,endpoint_id,is_healthy,created_at,response_time_ms,error_message,checked_at'])->limit(12)->get();
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

    public function monitorEndpoints(Client $client)
    {
     
        try {
            Artisan::call('app:monitor-endpoints', [
                '--client_id' => $client->id
            ]);
    
            return response()->json(['message' => "Monitoring jobs dispatched for {$client->email}"]);
        } catch (\Throwable $e) {
            \Log::error("Failed to dispatch monitor job for client {$client->id}: " . $e->getMessage());
            return response()->json(['message' => 'Monitoring failed.'], 500);
        }
    }

}
