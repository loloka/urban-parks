<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Qso extends Model
{
    protected $fillable = [
        'activation_id',
        'callsign',
        'station_callsign',
        'qso_date',
        'time_on',
        'band',
        'mode',
        'submode',
        'freq',
        'rst_sent',
        'rst_rcvd',
        'sig_info',
    ];

    protected $casts = [
        'qso_date' => 'date',
        'freq' => 'decimal:4',
    ];

    /**
     * Активация, в рамках которой проведена связь
     */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }

    /**
     * Scope: связи конкретного охотника
     */
    public function scopeWithHunter($query, string $callsign)
    {
        return $query->where('callsign', strtoupper($callsign));
    }

    /**
     * Scope: только QSO из одобренных активаций
     */
    public function scopeConfirmed($query)
    {
        return $query->whereHas('activation', fn ($q) => $q->where('status', 'approved'));
    }
}
