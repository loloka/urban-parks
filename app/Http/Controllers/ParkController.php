<?php

namespace App\Http\Controllers;

use App\Models\Park;
use App\Models\Activation;
use Illuminate\Http\Request;

class ParkController extends Controller
{
    /**
     * Главная страница
     */
    public function index()
    {
        $stats = [
            'total_parks' => Park::count(),
            'total_activations' => Activation::where('status', 'approved')->count(),
            'cities' => Park::distinct('city')->count('city'),
            'regions' => Park::distinct('region')->count('region'),
        ];

        // Последняя активация
        $latestActivation = Activation::with('park')
            ->where('status', 'approved')
            ->latest('activation_date')
            ->first();

        // Топ активаторов (по количеству парков)
        $topActivators = Activation::where('status', 'approved')
            ->select('callsign')
            ->selectRaw('COUNT(DISTINCT park_id) as parks_count')
            ->selectRaw('SUM(qso_count) as total_qso')
            ->selectRaw('COUNT(*) as activations_count')
            ->groupBy('callsign')
            ->orderByDesc('parks_count')
            ->orderByDesc('total_qso')
            ->limit(10)
            ->get();

        return view('welcome', compact('stats', 'latestActivation', 'topActivators'));
    }

    /**
     * Список всех парков (отдельная страница)
     */
    public function list()
    {
        $parks = Park::active()
            ->withCount(['activations as approved_activations_count' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->orderBy('region')
            ->orderBy('reference')
            ->get();

        // Группируем по городу для удобной навигации
        $parksByCity = $parks->groupBy('city');

        return view('parks.index', compact('parks', 'parksByCity'));
    }

    /**
     * API: Получить все парки
     */
    /**
     * API: Получить все парки
     */
    public function getParks(Request $request)
    {
        $query = Park::active();

        // Фильтр по городу
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Фильтр по региону
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $parks = $query
            ->with(['activations' => function ($q) {
                $q->where('status', 'approved')
                    ->latest('activation_date')
                    ->limit(1);
            }])
            ->withCount(['activations' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->select('id', 'reference', 'name', 'name_en', 'city', 'region', 'latitude', 'longitude', 'description', 'description_en', 'area')
            ->orderBy('reference')
            ->get()
            ->map(function ($park) {
                $locale = app()->getLocale();
                $latestActivation = $park->activations->first();
                return [
                    'id' => $park->id,
                    'reference' => $park->reference,
                    // Уже локализовано по текущему языку (сессия) — фронт берёт как есть
                    'name' => $park->getLocalizedName($locale),
                    'city' => $park->city,
                    'region' => $park->region,
                    'latitude' => $park->latitude,
                    'longitude' => $park->longitude,
                    'description' => $park->getLocalizedDescription($locale),
                    'area' => $park->area,
                    'activation_count' => $park->activations_count,
                    'latest_activation' => $latestActivation ? [
                        'callsign' => $latestActivation->callsign,
                        'date' => $latestActivation->activation_date->format('Y-m-d'),
                        'date_formatted' => $latestActivation->activation_date->format('d.m.Y'),
                        'date_human' => $latestActivation->activation_date->diffForHumans(),
                    ] : null,
                ];
            });

        return response()->json($parks);
    }

    /**
     * Страница парка
     */
    public function show(Park $park)
    {
        // Загружаем только одобренные активации + число публичных фото у каждой
        $park->load(['activations' => function ($query) {
            $query->where('status', 'approved')
                ->withCount(['proofs as photos_count' => function ($q) {
                    $q->where('type', 'photo');
                }])
                ->latest('activation_date')
                ->limit(10);
        }]);

        return view('parks.show', compact('park'));
    }

    /**
     * API: Список городов
     */
    public function getCities()
    {
        $cities = Park::active()
            ->select('city', \DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($cities);
    }

    /**
     * API: Список регионов
     */
    public function getRegions()
    {
        $regions = Park::active()
            ->select('region', \DB::raw('count(*) as count'))
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($regions);
    }
}
