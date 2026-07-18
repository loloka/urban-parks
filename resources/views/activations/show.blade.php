<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $activation->callsign }} · {{ $activation->park->reference }} - Urban Parks</title>
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
                    <a href="{{ route('parks.index') }}"
                        class="text-gray-700 hover:text-[--color-primary-600] transition">{{ __('ui.nav.parks') }}</a>
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
            @if ($activation->status !== 'approved')
                <div class="mb-4 inline-block bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg text-sm font-semibold">
                    ⚠ {{ __('ui.activation_page.pending_notice') }}
                </div>
            @endif
            <div class="flex flex-wrap items-center gap-4 mb-4">
                <a href="https://www.qrz.com/db/{{ $activation->callsign }}" target="_blank"
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-lg font-mono text-2xl md:text-3xl font-bold hover:bg-white/30 transition">
                    {{ $activation->callsign }}
                </a>
                <span class="text-blue-200 text-2xl">→</span>
                <a href="{{ route('park.show', $activation->park) }}"
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-lg font-mono text-xl font-bold hover:bg-white/30 transition">
                    {{ $activation->park->reference }}
                </a>
            </div>
            <p class="text-blue-100 text-lg">
                {{ __('ui.activation_page.in_park') }}
                «{{ $activation->park->getLocalizedName(app()->getLocale()) }}» ·
                {{ $activation->activation_date->format('d.m.Y') }}
            </p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left: photos + notes -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📷 {{ __('ui.activation_page.photos') }}</h2>
                        @if ($photos->count() > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach ($photos as $photo)
                                    <button type="button"
                                        onclick="openLightbox('{{ route('activations.photo', [$activation, $photo]) }}')"
                                        class="block aspect-square overflow-hidden rounded-lg bg-gray-100 hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-[--color-primary-600]">
                                        <img src="{{ route('activations.photo', [$activation, $photo]) }}"
                                            alt="{{ __('ui.activation_page.photos') }}" loading="lazy"
                                            class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">{{ __('ui.activation_page.no_photos') }}</p>
                        @endif
                    </div>

                    @if ($activation->notes)
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-3">💬 {{ __('ui.activation_page.notes') }}
                            </h2>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $activation->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Right: summary + actions -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📊 {{ __('ui.activation_page.log_summary') }}
                        </h3>
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="text-gray-600">{{ __('ui.activation_page.total_qso') }}</span>
                            <span class="font-bold text-2xl text-[--color-primary-600]">{{ $activation->qso_count }}</span>
                        </div>

                        @if ($summary['bands']->count())
                            <div class="py-3 border-b">
                                <div class="text-gray-600 mb-2">{{ __('ui.activation_page.bands') }}</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($summary['bands'] as $band => $count)
                                        <span
                                            class="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded-full font-mono">{{ $band }}
                                            · {{ $count }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($summary['modes']->count())
                            <div class="py-3 border-b">
                                <div class="text-gray-600 mb-2">{{ __('ui.activation_page.modes') }}</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($summary['modes'] as $mode => $count)
                                        <span
                                            class="text-sm px-2 py-1 bg-purple-100 text-purple-800 rounded-full font-mono">{{ $mode }}
                                            · {{ $count }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($summary['has_log'] && $summary['time_start'])
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">{{ __('ui.activation_page.time_utc') }}</span>
                                <span class="font-mono text-sm text-gray-900">
                                    {{ \Illuminate\Support\Str::of($summary['time_start'])->substr(0, 5) }}–{{ \Illuminate\Support\Str::of($summary['time_end'])->substr(0, 5) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 space-y-3">
                        @if ($summary['has_log'])
                            <a href="{{ route('activations.public_adif', $activation) }}"
                                class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('ui.activation_page.download_adif') }}
                            </a>
                        @else
                            <p class="text-sm text-gray-500 text-center">{{ __('ui.activation_page.no_log') }}</p>
                        @endif
                        <a href="{{ route('park.show', $activation->park) }}"
                            class="w-full px-4 py-3 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition font-semibold flex items-center justify-center gap-2">
                            📍 {{ $activation->park->reference }}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Lightbox -->
    <div id="lightbox"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 p-4 cursor-zoom-out"
        onclick="closeLightbox()">
        <img id="lightbox-img" src="" alt="" class="max-h-full max-w-full rounded-lg shadow-2xl">
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="border-t border-gray-800 pt-6 text-center text-gray-400">
                <p>© 2025 Urban Parks. {{ __('ui.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

    <script>
        const lb = document.getElementById('lightbox');
        const lbImg = document.getElementById('lightbox-img');

        function openLightbox(src) {
            lbImg.src = src;
            lb.classList.remove('hidden');
            lb.classList.add('flex');
        }

        function closeLightbox() {
            lb.classList.add('hidden');
            lb.classList.remove('flex');
            lbImg.src = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>

</body>

</html>
