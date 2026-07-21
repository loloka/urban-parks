<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $park->getLocalizedName(app()->getLocale()) }} - Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css" />
</head>

<body class="bg-gray-50">

    <!-- Header - ЕДИНЫЙ ДЛЯ ВСЕХ СТРАНИЦ -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center space-x-4 group">
                    <div
                        class="w-12 h-12 bg-[--color-primary-600] rounded-lg flex items-center justify-center group-hover:bg-[--color-primary-700] transition">
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
                    <a href="/"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.home') }}</a>
                    <a href="/#map"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.map.title') }}</a>
                    <a href="{{ route('parks.index') }}"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.nav.parks') }}</a>
                    <a href="/#top" class="text-gray-700 hover:text-[--color-primary-600] transition">🏆
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

    <!-- Park Hero -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex items-center space-x-4 mb-6">
                <span class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-lg font-mono text-2xl font-bold">
                    {{ $park->reference }}
                </span>
                <span class="px-3 py-1 bg-green-500 rounded-full text-sm font-semibold">
                    ✓ {{ $park->status === 'active' ? __('ui.active') : __('ui.inactive') }}
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $park->getLocalizedName(app()->getLocale()) }}</h1>
            <div class="flex flex-wrap gap-4 text-blue-100">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    {{ $park->city }}, {{ $park->region }}
                </div>
                @if ($park->area)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" />
                        </svg>
                        {{ __('ui.park.area') }}: {{ $park->area }}
                    </div>
                @endif
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    {{ __('ui.park.activations') }}: {{ $park->activation_count }}
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Description -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📝 {{ __('ui.description') }}</h2>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $park->getLocalizedDescription(app()->getLocale()) ?? __('Urban park for ham radio activations') }}
                        </p>
                    </div>

                    <!-- Map -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">🗺️ {{ __('ui.park.location') }}</h2>
                        <div id="park-map" style="height: 400px; border-radius: 8px;"></div>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded">
                                <span class="text-gray-600">{{ __('ui.latitude') }}:</span>
                                <span class="font-mono font-semibold ml-2">{{ $park->latitude }}</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <span class="text-gray-600">{{ __('ui.longitude') }}:</span>
                                <span class="font-mono font-semibold ml-2">{{ $park->longitude }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activations -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📡 {{ __('ui.park.activations') }}</h2>
                        @if ($park->activations->count() > 0)
                            <div class="space-y-4">
                                @foreach ($park->activations as $activation)
                                    <div class="border-l-4 border-blue-500 pl-4 py-2 bg-gray-50 rounded-r">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <a href="https://www.qrz.com/db/{{ $activation->callsign }}"
                                                    target="_blank"
                                                    class="font-mono font-bold text-lg text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $activation->callsign }}
                                                </a>
                                                <span class="text-sm text-gray-500 ml-2">{{ $activation->qso_count }}
                                                    {{ __('ui.park.qso_count') }}</span>
                                                @if ($activation->photos_count)
                                                    <span class="text-sm text-gray-500 ml-2"
                                                        title="{{ __('ui.activation_page.photos') }}">📷
                                                        {{ $activation->photos_count }}</span>
                                                @endif
                                            </div>
                                            <span
                                                class="text-sm text-gray-600">{{ $activation->activation_date->format('d.m.Y') }}</span>
                                        </div>
                                        @if ($activation->notes)
                                            <p class="text-sm text-gray-600 mt-1">💬 {{ $activation->notes }}</p>
                                        @endif
                                        <div class="mt-2">
                                            <a href="{{ route('activations.show', $activation) }}"
                                                class="text-sm font-semibold text-[--color-primary-600] hover:underline inline-flex items-center gap-1">
                                                {{ __('ui.activation_page.view') }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                <p class="text-lg font-semibold mb-2">{{ __('ui.park.no_activations') }}</p>
                                <p class="text-sm">{{ __('ui.park.be_first_activator') }} 🎯</p>
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Right Column - Sidebar -->
                <div class="space-y-6">

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">⚡ {{ __('ui.actions') }}</h3>
                        <div class="space-y-3">
                            <a href="{{ route('activations.create') }}"
                                class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('ui.park.add_activation') }}
                            </a>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📊 {{ __('ui.statistics') }}</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-gray-600">{{ __('ui.total_activations') }}:</span>
                                <span
                                    class="font-bold text-2xl text-[--color-primary-600]">{{ $park->activation_count }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-gray-600">{{ __('ui.unique_callsigns') }}:</span>
                                <span
                                    class="font-bold text-2xl text-green-600">{{ $park->activations->unique('callsign')->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">{{ __('ui.total_qso') }}:</span>
                                <span
                                    class="font-bold text-2xl text-purple-600">{{ $park->activations->sum('qso_count') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-blue-900 mb-3">💡 {{ __('ui.activation_rules') }}</h3>
                        <ul class="space-y-2 text-sm text-blue-800">
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>{{ __('ui.minimum_10_qso') }}</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>{{ __('ui.work_on_amateur_bands') }}</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>{{ __('ui.respect_park_visitors') }}</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">✓</span>
                                <span>{{ __('ui.portable_equipment') }}</span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- Footer - ЕДИНЫЙ ДЛЯ ВСЕХ СТРАНИЦ -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
                <div>
                    <h4 class="text-lg font-bold mb-4">Urban Parks</h4>
                    <p class="text-gray-400">{{ __('ui.hero.subtitle') }}</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">{{ __('ui.navigation') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="/"
                                class="text-gray-400 hover:text-white transition">{{ __('ui.home') }}</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-white transition">{{ __('ui.footer.about') }}</a></li>
                        <li><a href="{{ route('rules') }}"
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
                <p>© 2025 Urban Parks. {{ __('ui.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js"></script>
    <script>
        const parkMap = new maplibregl.Map({
            container: 'park-map',
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [{{ $park->longitude }}, {{ $park->latitude }}],
            zoom: 13,
            cooperativeGestures: true,
            attributionControl: {
                compact: true
            },
            @if (app()->getLocale() === 'ru')
            locale: {
                'CooperativeGesturesHandler.WindowsHelpText': 'Зум карты: Ctrl + колесо мыши',
                'CooperativeGesturesHandler.MacHelpText': 'Зум карты: ⌘ + колесо мыши',
                'CooperativeGesturesHandler.MobileHelpText': 'Двигайте карту двумя пальцами',
            },
            @endif
        });

        parkMap.addControl(new maplibregl.NavigationControl({
            showCompass: false
        }), 'top-right');

        new maplibregl.Marker({
                color: '#2563eb'
            })
            .setLngLat([{{ $park->longitude }}, {{ $park->latitude }}])
            .setPopup(
                new maplibregl.Popup({
                    offset: 24
                }).setHTML(`
                    <div class="p-2">
                        <h4 class="font-bold text-lg">{{ $park->getLocalizedName(app()->getLocale()) }}</h4>
                        <p class="text-sm font-mono">{{ $park->reference }}</p>
                    </div>
                `)
            )
            .addTo(parkMap)
            .togglePopup();
    </script>

</body>

</html>
