<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Endpoint extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'url', 'is_active'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
