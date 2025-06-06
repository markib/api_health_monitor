<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Client;


Route::get('/', function () {
    $clients = Client::all();
    return Inertia::render('App', ['clients' => $clients]);
});


use Illuminate\Support\Facades\Redis;

Route::get('/test-redis', function () {
    try {
        $ping = Redis::connection()->ping();
        return response()->json(['status' => 'Redis connected', 'response' => $ping]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'Redis failed', 'error' => $e->getMessage()], 500);
    }
});