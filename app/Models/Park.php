<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Park extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'city',
        'region',
        'latitude',
        'longitude',
        'description',
        'area',
        'status',
        'activation_count'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'activation_count' => 'integer',
    ];

    /**
     * Активации парка
     */
    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }

    /**
     * Последние активации
     */
    public function recentActivations($limit = 5)
    {
        return $this->activations()
            ->orderBy('activation_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Уникальные активаторы
     */
    public function uniqueActivators()
    {
        return $this->activations()
            ->distinct()
            ->count('callsign');
    }

    /**
     * Общее количество QSO
     */
    public function totalQsoCount()
    {
        return $this->activations()->sum('qso_count');
    }

    /**
     * Scope для активных парков
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope для поиска по городу
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope для поиска по региону
     */
    public function scopeInRegion($query, $region)
    {
        return $query->where('region', $region);
    }
}