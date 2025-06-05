<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Client;


Route::get('/', function () {
    $clients = Client::all();
    return Inertia::render('App', ['clients' => $clients]);
});
