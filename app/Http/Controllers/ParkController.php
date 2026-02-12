<?php

namespace App\Http\Controllers;

use App\Models\Park;
use Illuminate\Http\Request;

class ParkController extends Controller
{
    /**
     * Главная страница
     */
    public function index()
    {
        $stats = [
            'total_parks' => Park::active()->count(),
            'total_activations' => Park::sum('activation_count'),
            'cities' => Park::distinct()->count('city'),
            'regions' => Park::distinct()->count('region'),
        ];

        return view('welcome', compact('stats'));
    }

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
            ->select('id', 'reference', 'name', 'city', 'region', 'latitude', 'longitude', 'description', 'area', 'activation_count')
            ->orderBy('reference')
            ->get();

        return response()->json($parks);
    }

    /**
     * Страница парка
     */
    public function show(Park $park)
    {
        $park->load(['activations' => function ($query) {
            $query->orderBy('activation_date', 'desc')->limit(10);
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
