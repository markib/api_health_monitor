<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Client;
use Illuminate\Support\Facades\Redis;

// Route::get('/', function () {
//     $clients = Client::all();
//     return Inertia::render('App', ['clients' => $clients]);
// });

Route::get('/', function () {
    return Inertia::render('Dashboard'); // Renders your Dashboard.vue component
});




Route::get('/test-redis', function () {
    try {
        $ping = Redis::connection()->ping();
        return response()->json(['status' => 'Redis connected', 'response' => $ping]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'Redis failed', 'error' => $e->getMessage()], 500);
    }
});