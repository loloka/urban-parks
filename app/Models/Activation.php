<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activation extends Model
{
    protected $fillable = [
        'park_id',
        'callsign',
        'activation_date',
        'qso_count',
        'notes'
    ];

    protected $casts = [
        'activation_date' => 'date',
        'qso_count' => 'integer',
    ];

    /**
     * Парк активации
     */
    public function park(): BelongsTo
    {
        return $this->belongsTo(Park::class);
    }

    /**
     * Scope для активаций по позывному
     */
    public function scopeByCallsign($query, $callsign)
    {
        return $query->where('callsign', $callsign);
    }

    /**
     * Scope для последних активаций
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('activation_date', '>=', now()->subDays($days));
    }
}