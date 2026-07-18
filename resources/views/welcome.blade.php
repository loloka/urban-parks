<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urban Parks - {{ __('ui.hero.subtitle') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- MapLibre GL JS -->
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css" />
</head>

<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center space-x-4 group">
                    <div class="w-12 h-12 bg-[--color-primary-600] rounded-lg flex items-center justify-center group-hover:bg-[--color-primary-700] transition">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 group-hover:text-[--color-primary-600] transition">Urban Parks</h1>
                        <p class="text-sm text-gray-600">{{ __('ui.hero.subtitle') }}</p>
                    </div>
                </a>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="#map"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.nav.map') }}</a>
                    <a href="{{ route('parks.index') }}"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.nav.parks') }}</a>
                    <a href="#top" class="text-gray-700 hover:text-[--color-primary-600] transition">🏆
                        {{ __('ui.nav.top') }}</a>
                    <a href="{{ route('activations.create') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        ➕ {{ __('ui.nav.add_activation') }}
                    </a>
                    <a href="#"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.nav.diplomas') }}</a>

                    @auth
                        <a href="{{ route('cabinet') }}"
                            class="text-gray-700 hover:text-[--color-primary-600] transition font-semibold">👤
                            {{ __('ui.nav.cabinet') }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-gray-600 hover:text-red-600 transition font-semibold">{{ __('ui.nav.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-gray-700 hover:text-[--color-primary-600] transition font-semibold">{{ __('ui.nav.login') }}</a>
                    @endauth

                    <!-- Language Switcher -->
                    <div class="flex items-center gap-2 ml-4 border-l pl-4">
                        <a href="?lang=ru"
                            class="px-2 py-1 rounded {{ app()->getLocale() === 'ru' ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">
                            RU
                        </a>
                        <a href="?lang=en"
                            class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">
                            EN
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                🎯 {{ __('ui.hero.title') }}
            </h2>
            <p class="text-xl md:text-2xl text-blue-100 mb-8">
                {{ __('ui.hero.subtitle') }} 🌍
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['total_parks'] }}</div>
                    <div class="text-sm text-blue-200">{{ __('ui.hero.stats.parks') }}</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['total_activations'] }}</div>
                    <div class="text-sm text-blue-200">{{ __('ui.hero.stats.activations') }}</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['cities'] }}</div>
                    <div class="text-sm text-blue-200">{{ __('ui.hero.stats.cities') }}</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['regions'] }}</div>
                    <div class="text-sm text-blue-200">{{ __('ui.hero.stats.regions') }}</div>
                </div>
            </div>

            <div class="mt-8 flex flex-col md:flex-row items-center justify-center gap-4">
                @if ($featuredCity)
                    <div class="inline-block bg-yellow-400 text-gray-900 px-6 py-3 rounded-full font-bold">
                        🏆 {{ __('ui.hero.featured_city', ['city' => $featuredCity->city, 'count' => $featuredCity->parks_count]) }}
                    </div>
                @endif

                @if ($latestActivation)
                    <div
                        class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full font-semibold">
                        <span class="text-green-300">🔥 {{ __('ui.hero.latest_activation') }}</span>
                        <a href="https://www.qrz.com/db/{{ $latestActivation->callsign }}" target="_blank"
                            class="font-mono font-bold hover:underline">
                            {{ $latestActivation->callsign }}
                        </a>
                        <span class="text-blue-200">→</span>
                        <a href="/park/{{ $latestActivation->park->id }}" class="hover:underline">
                            {{ $latestActivation->park->reference }}
                        </a>
                        <span
                            class="text-xs text-blue-200">({{ $latestActivation->activation_date->diffForHumans() }})</span>
                        <a href="{{ route('activations.show', $latestActivation) }}"
                            class="ml-1 underline decoration-dotted hover:text-white"
                            title="{{ __('ui.activation_page.view') }}">👁</a>
                    </div>
                @endif
            </div>
            <div class="mt-6">
                <a href="{{ route('activations.create') }}"
                    class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('ui.nav.add_activation') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-12" id="map">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-gray-900">🗺️ {{ __('ui.map.title') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        <select id="city-filter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--color-primary-600] focus:border-transparent">
                            <option value="">{{ __('ui.map.filter_city') }}</option>
                        </select>
                        <select id="region-filter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--color-primary-600] focus:border-transparent">
                            <option value="">{{ __('ui.map.filter_region') }}</option>
                        </select>
                        <input type="text" id="search-input" placeholder="🔍 {{ __('ui.map.search_placeholder') }}"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--color-primary-600] focus:border-transparent" />
                    </div>
                </div>

                <div id="map" class="rounded-xl"></div>

                <div class="mt-4 text-sm text-gray-600 flex items-center justify-between">
                    <div>
                        <span class="font-semibold" id="visible-parks-count">0</span>
                        {{ __('ui.map.visible_parks') }}
                    </div>
                    <button id="reset-filters" class="text-[--color-primary-600] hover:underline">
                        {{ __('ui.map.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Activators -->
    <section class="py-12 bg-white" id="top">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <h3 class="text-3xl font-bold text-gray-900 mb-2">🏆 {{ __('ui.top.title') }}</h3>
                <p class="text-gray-600">{{ __('ui.top.subtitle') }}</p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('ui.top.rank') }}</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('ui.top.callsign') }}</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">{{ __('ui.top.parks') }}</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">{{ __('ui.top.activations') }}
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">{{ __('ui.top.qso') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($topActivators as $index => $activator)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        @if ($index === 0)
                                            <span class="text-2xl">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">🥉</span>
                                        @else
                                            <span class="text-gray-500 font-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="https://www.qrz.com/db/{{ $activator->callsign }}" target="_blank"
                                            class="font-mono font-bold text-lg text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $activator->callsign }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center justify-center px-3 py-1 bg-green-100 text-green-800 rounded-full font-bold text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            {{ $activator->parks_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center justify-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7" />
                                            </svg>
                                            {{ $activator->activations_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="font-bold text-gray-900 text-lg">{{ number_format($activator->total_qso) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="text-lg font-semibold">{{ __('ui.top.no_activators') }}</p>
                                        <p class="text-sm mt-2">{{ __('ui.top.be_first') }} 🚀</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($topActivators->count() >= 10)
                    <div class="text-center mt-6">
                        <a href="#"
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold transition">
                            {{ __('ui.top.view_all') }}
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Parks List -->
    <section class="py-12 bg-gray-50" id="parks">
        <div class="container mx-auto px-4">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">📍 {{ __('ui.parks.title') }}</h3>
            <div id="parks-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="col-span-full text-center py-8">
                    <div
                        class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[--color-primary-600]">
                    </div>
                    <p class="mt-4 text-gray-600">{{ __('ui.parks.loading') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h3 class="text-3xl font-bold text-center text-gray-900 mb-12">{{ __('ui.features.title') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-2">{{ __('ui.features.accessibility.title') }}</h4>
                    <p class="text-gray-600">{{ __('ui.features.accessibility.text') }}</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-2">{{ __('ui.features.diplomas.title') }}</h4>
                    <p class="text-gray-600">{{ __('ui.features.diplomas.text') }}</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-2">{{ __('ui.features.community.title') }}</h4>
                    <p class="text-gray-600">{{ __('ui.features.community.text') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
                <div>
                    <h4 class="text-lg font-bold mb-4">Urban Parks</h4>
                    <p class="text-gray-400">{{ __('ui.hero.subtitle') }}</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">{{ __('ui.nav.map') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="#"
                                class="text-gray-400 hover:text-white transition">{{ __('ui.footer.about') }}</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-white transition">{{ __('ui.footer.rules') }}</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-white transition">{{ __('ui.nav.diplomas') }}</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-white transition">{{ __('ui.footer.api') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">{{ __('ui.footer.contacts') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📧 info@urbanparks.ru</li>
                        <li>📱 Telegram: @urbanparks</li>
                        <li>🌐 VK: vk.com/urbanparks</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 text-center text-gray-400">
                <p>© 2026 Urban Parks. {{ __('ui.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

    <!-- MapLibre GL JS -->
    <script src="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js"></script>

    <script>
        // Передаём переводы в JS
        const translations = {
            parksNotFound: "{{ __('ui.parks.not_found') }}",
            tryFilters: "{{ __('ui.parks.try_filters') }}",
            notActivated: "{{ __('ui.parks.not_activated') }}",
            moreDetails: "{{ __('ui.parks.more_details') }}",
            activationsCount: "{{ __('ui.parks.activations_count') }}",
            area: "{{ __('ui.park.area') }}",
        };

        let allParks = [];
        let filteredParks = [];
        const currentLocale = "{{ app()->getLocale() }}";

        // Инициализация карты (MapLibre GL + векторные тайлы OpenFreeMap).
        // cooperativeGestures: колесо мыши скроллит страницу, зум — Ctrl+колесо,
        // на телефоне карта двигается двумя пальцами — страница не «залипает».
        const map = new maplibregl.Map({
            container: 'map',
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [82.914517, 55.751244],
            zoom: 4,
            cooperativeGestures: true,
            attributionControl: {
                compact: true
            },
            locale: currentLocale === 'ru' ? {
                'CooperativeGesturesHandler.WindowsHelpText': 'Зум карты: Ctrl + колесо мыши',
                'CooperativeGesturesHandler.MacHelpText': 'Зум карты: ⌘ + колесо мыши',
                'CooperativeGesturesHandler.MobileHelpText': 'Двигайте карту двумя пальцами',
            } : {},
        });

        map.addControl(new maplibregl.NavigationControl({
            showCompass: false
        }), 'top-right');
        map.addControl(new maplibregl.FullscreenControl(), 'top-right');

        let mapReady = false;
        let pendingParks = null;

        const parksToGeoJSON = (parks) => ({
            type: 'FeatureCollection',
            features: parks.map(park => ({
                type: 'Feature',
                geometry: {
                    type: 'Point',
                    coordinates: [Number(park.longitude), Number(park.latitude)]
                },
                properties: {
                    id: park.id,
                    name: (currentLocale === 'en' && park.name_en) ? park.name_en : park.name,
                    reference: park.reference,
                    city: park.city,
                    region: park.region,
                    area: park.area || '',
                    activation_count: park.activation_count || 0,
                },
            })),
        });

        map.on('load', () => {
            map.addSource('parks', {
                type: 'geojson',
                data: parksToGeoJSON([]),
                cluster: true,
                clusterMaxZoom: 12,
                clusterRadius: 50,
            });

            // Кластеры
            map.addLayer({
                id: 'clusters',
                type: 'circle',
                source: 'parks',
                filter: ['has', 'point_count'],
                paint: {
                    'circle-color': '#2563eb',
                    'circle-radius': ['step', ['get', 'point_count'], 18, 10, 24, 30, 30],
                    'circle-stroke-width': 3,
                    'circle-stroke-color': '#ffffff',
                },
            });

            map.addLayer({
                id: 'cluster-count',
                type: 'symbol',
                source: 'parks',
                filter: ['has', 'point_count'],
                layout: {
                    'text-field': ['get', 'point_count_abbreviated'],
                    'text-font': ['Noto Sans Regular'],
                    'text-size': 13,
                },
                paint: {
                    'text-color': '#ffffff'
                },
            });

            // Одиночные парки
            map.addLayer({
                id: 'park-points',
                type: 'circle',
                source: 'parks',
                filter: ['!', ['has', 'point_count']],
                paint: {
                    'circle-color': '#2563eb',
                    'circle-radius': 9,
                    'circle-stroke-width': 3,
                    'circle-stroke-color': '#ffffff',
                },
            });

            // Клик по кластеру — приблизить
            map.on('click', 'clusters', async (e) => {
                const features = map.queryRenderedFeatures(e.point, {
                    layers: ['clusters']
                });
                const zoom = await map.getSource('parks').getClusterExpansionZoom(
                    features[0].properties.cluster_id
                );
                map.easeTo({
                    center: features[0].geometry.coordinates,
                    zoom: zoom + 0.5
                });
            });

            // Клик по парку — попап
            map.on('click', 'park-points', (e) => {
                const p = e.features[0].properties;

                new maplibregl.Popup({
                        offset: 12,
                        maxWidth: '280px'
                    })
                    .setLngLat(e.features[0].geometry.coordinates)
                    .setHTML(`
                        <div class="p-2 min-w-[200px]">
                            <h4 class="font-bold text-lg mb-1">${p.name}</h4>
                            <p class="text-sm text-gray-600 font-mono mb-1">${p.reference}</p>
                            <p class="text-sm mb-1">📍 ${p.city}, ${p.region}</p>
                            ${p.area ? `<p class="text-xs text-gray-500">${translations.area}: ${p.area}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-2">⚡ ${translations.activationsCount}: ${p.activation_count}</p>
                            <a href="/park/${p.id}" class="text-blue-600 text-sm hover:underline mt-2 inline-block">${translations.moreDetails} →</a>
                        </div>
                    `)
                    .addTo(map);
            });

            ['clusters', 'park-points'].forEach(layer => {
                map.on('mouseenter', layer, () => map.getCanvas().style.cursor = 'pointer');
                map.on('mouseleave', layer, () => map.getCanvas().style.cursor = '');
            });

            mapReady = true;
            if (pendingParks) {
                updateMap(pendingParks);
                pendingParks = null;
            }
        });

        // Загружаем парки
        fetch('/api/parks')
            .then(response => response.json())
            .then(parks => {
                allParks = parks;

                const sortedParks = [...parks].sort((a, b) => {
                    if (!a.latest_activation && !b.latest_activation) return 0;
                    if (!a.latest_activation) return 1;
                    if (!b.latest_activation) return -1;
                    return new Date(b.latest_activation.date) - new Date(a.latest_activation.date);
                });

                filteredParks = sortedParks;
                updateMap(sortedParks);
                // «Последние добавленные» — самые новые парки (по возрастанию id → новые выше)
                const recentlyAdded = [...parks].sort((a, b) => b.id - a.id).slice(0, 6);
                renderParksList(recentlyAdded);
            })
            .catch(error => {
                console.error('Error loading parks:', error);
                document.getElementById('parks-list').innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-600">
                        ❌ {{ __('ui.error') }}
                    </div>
                `;
            });

        // Загружаем города
        fetch('/api/cities')
            .then(response => response.json())
            .then(cities => {
                const select = document.getElementById('city-filter');
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.city;
                    option.textContent = `${city.city} (${city.count})`;
                    select.appendChild(option);
                });
            });

        // Загружаем регионы
        fetch('/api/regions')
            .then(response => response.json())
            .then(regions => {
                const select = document.getElementById('region-filter');
                regions.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.region;
                    option.textContent = `${region.region} (${region.count})`;
                    select.appendChild(option);
                });
            });

        // Обновление карты
        function updateMap(parks) {
            document.getElementById('visible-parks-count').textContent = parks.length;

            if (!mapReady) {
                pendingParks = parks; // карта ещё грузится — отдадим данные в on('load')
                return;
            }

            map.getSource('parks').setData(parksToGeoJSON(parks));
        }

        // Рендер списка парков
        function renderParksList(parks) {
            const container = document.getElementById('parks-list');

            if (parks.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg font-semibold">${translations.parksNotFound}</p>
                        <p class="text-sm mt-2">${translations.tryFilters}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = parks.map(park => {
                // name и description уже приходят из API локализованными по текущему языку
                const parkName = park.name;
                const parkDescription = park.description;

                return `
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition transform hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full font-mono">
                                    ${park.reference}
                                </span>
                            </div>
                            <span class="text-xs text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                ${park.activation_count || 0}
                            </span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">${parkName}</h4>
                        <p class="text-sm text-gray-600 mb-1 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            ${park.city}, ${park.region}
                        </p>
                        ${park.area ? `<p class="text-xs text-gray-500 mb-2">📐 ${park.area}</p>` : ''}
                        
                        ${park.latest_activation ? `
                                <div class="flex items-center gap-2 mb-3 text-sm bg-green-50 px-3 py-2 rounded-lg">
                                    <span class="text-green-600">🔥</span>
                                    <a href="https://www.qrz.com/db/${park.latest_activation.callsign}" 
                                       target="_blank"
                                       class="font-mono font-bold text-blue-600 hover:underline">
                                        ${park.latest_activation.callsign}
                                    </a>
                                    <span class="text-xs text-gray-500">${park.latest_activation.date_human}</span>
                                </div>
                            ` : `<div class="mb-3 text-sm text-gray-400 italic">⚡ ${translations.notActivated}</div>`}
                        
                        <p class="text-sm text-gray-700 mb-4 line-clamp-2">${parkDescription || 'Urban park for ham radio activations'}</p>
                        <a href="/park/${park.id}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold text-sm transition">
                            ${translations.moreDetails}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                `;
            }).join('');
        }

        // Фильтрация
        function applyFilters() {
            const cityFilter = document.getElementById('city-filter').value;
            const regionFilter = document.getElementById('region-filter').value;
            const searchQuery = document.getElementById('search-input').value.toLowerCase();

            filteredParks = allParks.filter(park => {
                const parkName = currentLocale === 'en' && park.name_en ? park.name_en : park.name;
                const matchCity = !cityFilter || park.city === cityFilter;
                const matchRegion = !regionFilter || park.region === regionFilter;
                const matchSearch = !searchQuery ||
                    parkName.toLowerCase().includes(searchQuery) ||
                    park.reference.toLowerCase().includes(searchQuery) ||
                    park.city.toLowerCase().includes(searchQuery);

                return matchCity && matchRegion && matchSearch;
            });

            updateMap(filteredParks);
            renderParksList(filteredParks.slice(0, 6));
        }

        document.getElementById('city-filter').addEventListener('change', applyFilters);
        document.getElementById('region-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('input', applyFilters);

        document.getElementById('reset-filters').addEventListener('click', () => {
            document.getElementById('city-filter').value = '';
            document.getElementById('region-filter').value = '';
            document.getElementById('search-input').value = '';
            applyFilters();
        });
    </script>

</body>

</html>
