<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationProof extends Model
{
    public const TYPE_PHOTO = 'photo';
    public const TYPE_SCREENSHOT = 'screenshot';
    public const TYPE_GPX = 'gpx';

    protected $fillable = [
        'activation_id',
        'type',
        'path',
        'original_name',
        'size',
    ];

    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }
}
