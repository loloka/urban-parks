<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.parks_index.title') }} - Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

    <!-- Header -->
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
                        <h1 class="text-2xl font-bold text-gray-900 group-hover:text-[--color-primary-600] transition">
                            Urban Parks</h1>
                        <p class="text-sm text-gray-600">{{ __('ui.hero.subtitle') }}</p>
                    </div>
                </a>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="/"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.home') }}</a>
                    <a href="/#map"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.nav.map') }}</a>
                    <a href="{{ route('activations.create') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        ➕ {{ __('ui.nav.add_activation') }}
                    </a>
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
                    <div class="flex items-center gap-2 ml-4 border-l pl-4">
                        <a href="?lang=ru"
                            class="px-2 py-1 rounded {{ app()->getLocale() === 'ru' ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">RU</a>
                        <a href="?lang=en"
                            class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-blue-100 text-blue-800 font-bold' : 'text-gray-600 hover:bg-gray-100' }}">EN</a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-2">📍 {{ __('ui.parks_index.title') }}</h1>
            <p class="text-blue-100 text-lg">{{ __('ui.parks_index.subtitle') }} — {{ $parks->count() }}</p>
        </div>
    </section>

    <!-- Parks -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            @forelse ($parksByCity as $city => $cityParks)
                <div class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>🏙️</span> {{ $city }}
                        <span class="text-base font-normal text-gray-500">({{ $cityParks->count() }})</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($cityParks as $park)
                            <a href="{{ route('park.show', $park) }}"
                                class="block bg-white rounded-xl shadow-md hover:shadow-xl transition p-6 border-l-4 border-[--color-primary-600] group">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="font-mono font-bold text-[--color-primary-600]">{{ $park->reference }}</span>
                                    <span
                                        class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">📡
                                        {{ $park->approved_activations_count }}
                                        {{ __('ui.parks_index.activations') }}</span>
                                </div>
                                <h3
                                    class="text-lg font-bold text-gray-900 mb-1 group-hover:text-[--color-primary-600] transition">
                                    {{ $park->getLocalizedName(app()->getLocale()) }}
                                </h3>
                                <p class="text-sm text-gray-600 line-clamp-2">
                                    {{ $park->getLocalizedDescription(app()->getLocale()) }}
                                </p>
                                @if ($park->area)
                                    <p class="text-xs text-gray-400 mt-2">📐 {{ $park->area }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-gray-500">
                    <p class="text-lg font-semibold">{{ __('ui.parks_index.empty') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="border-t border-gray-800 pt-6 text-center text-gray-400">
                <p>© 2025 Urban Parks. {{ __('ui.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

</body>

</html>
