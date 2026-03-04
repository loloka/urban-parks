<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Park extends Model
{
    protected $fillable = [
        'reference',
        'country_code',
        'region_code',
        'name',
        'name_en',
        'description',
        'description_en',
        'city',
        'region',
        'latitude',
        'longitude',
        'area',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'activation_count' => 'integer',
    ];

    /**
     * Boot модели - автогенерация reference
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($park) {
            if (empty($park->reference)) {
                // Формат: UP-RU-NSK-0001
                $countryCode = $park->country_code ?? 'RU';
                $regionCode = $park->region_code ?? 'XXX';

                // Находим последний номер в регионе
                $lastPark = Park::where('country_code', $countryCode)
                    ->where('region_code', $regionCode)
                    ->orderBy('reference', 'desc')
                    ->first();

                if ($lastPark && preg_match('/UP-' . $countryCode . '-' . $regionCode . '-(\d+)/', $lastPark->reference, $matches)) {
                    $nextNumber = intval($matches[1]) + 1;
                } else {
                    $nextNumber = 1;
                }

                $park->reference = sprintf('UP-%s-%s-%04d', $countryCode, $regionCode, $nextNumber);
            }
        });
    }

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
            ->where('status', 'approved')
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
            ->where('status', 'approved')
            ->distinct()
            ->count('callsign');
    }

    /**
     * Общее количество QSO
     */
    public function totalQsoCount()
    {
        return $this->activations()
            ->where('status', 'approved')
            ->sum('qso_count');
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

    /**
     * Scope для поиска по стране
     */
    public function scopeInCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Получить полное название парка (с учётом языка)
     */
    public function getLocalizedName($locale = 'ru')
    {
        if ($locale === 'en' && !empty($this->name_en)) {
            return $this->name_en;
        }
        return $this->name;
    }

    /**
     * Получить описание парка (с учётом языка)
     */
    public function getLocalizedDescription($locale = 'ru')
    {
        if ($locale === 'en' && !empty($this->description_en)) {
            return $this->description_en;
        }
        return $this->description;
    }
}
