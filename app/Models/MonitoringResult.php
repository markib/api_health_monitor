<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonitoringResult extends Model
{
    use HasFactory;
    
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

    protected $appends = ['human_time'];

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class);
    }

    public function getHumanTimeAttribute()
    {
        return $this->checked_at ? $this->checked_at->diffForHumans() : null;
    }
}
