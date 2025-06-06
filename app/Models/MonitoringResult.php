<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringResult extends Model
{
    protected $fillable = [
        'endpoint_id',
        'status_code',
        'response_time_ms',
        'is_healthy',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'is_healthy' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class);
    }
}
