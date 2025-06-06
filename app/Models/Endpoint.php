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
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function monitoringResults()
    {
        return $this->hasMany(MonitoringResult::class);
    }
    public function latestResult()
    {
        return $this->hasOne(MonitoringResult::class);
    }
}
