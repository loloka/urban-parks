<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activation extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_ADIF = 'adif';

    protected $fillable = [
        'park_id',
        'user_id',
        'callsign',
        'activation_date',
        'qso_count',
        'notes',
        'status',
        'moderator_note',
        'adif_path',
        'source',
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
     * Пользователь, загрузивший лог (null для старых записей)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связи (QSO) из загруженного ADIF-лога
     */
    public function qsos(): HasMany
    {
        return $this->hasMany(Qso::class);
    }

    /**
     * Доказательства присутствия в парке (фото, скриншоты, GPX)
     */
    public function proofs(): HasMany
    {
        return $this->hasMany(ActivationProof::class);
    }

    /**
     * Только одобренные активации
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope для активаций по позывному
     */
    public function scopeByCallsign($query, $callsign)
    {
        return $query->where('callsign', strtoupper($callsign));
    }

    /**
     * Scope для последних активаций
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('activation_date', '>=', now()->subDays($days));
    }
}
