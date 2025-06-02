<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Return all clients (only emails and ids)
    public function index()
    {
        return Client::select('id', 'email')->get();
    }

    // Return endpoints for one client
    public function show(Client $client)
    {
        return $client->endpoints()->select('id', 'url')->get();
    }
}
