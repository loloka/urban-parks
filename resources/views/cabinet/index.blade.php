<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <div
                        class="w-10 h-10 bg-[--color-primary-600] rounded-lg flex items-center justify-center group-hover:bg-[--color-primary-700] transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span
                        class="text-xl font-bold text-gray-900 group-hover:text-[--color-primary-600] transition">Urban
                        Parks</span>
                </a>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline text-sm text-gray-600">
                        {{ auth()->user()->name }}
                        <span class="font-mono font-bold text-gray-900">{{ auth()->user()->callsign }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-sm text-gray-600 hover:text-red-600 transition">Выйти</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Мои активации</h1>
                <p class="text-gray-500">Загруженные логи и их статус модерации</p>
            </div>
            <a href="{{ route('activations.create') }}"
                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-5 py-3 rounded-lg transition">
                ➕ Загрузить активацию
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @unless (auth()->user()->hasVerifiedEmail())
            <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg mb-6 flex flex-wrap items-center justify-between gap-3">
                <span>📬 Подтвердите email, чтобы загружать активации.</span>
                <a href="{{ route('verification.notice') }}"
                    class="font-semibold underline hover:no-underline">Подтвердить →</a>
            </div>
        @endunless

        @forelse ($activations as $activation)
            <div class="bg-white rounded-xl shadow-sm p-5 mb-4 border-l-4
                @if ($activation->status === 'approved') border-green-500
                @elseif ($activation->status === 'rejected') border-red-500
                @else border-yellow-400 @endif">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-mono font-bold text-[--color-primary-600]">
                                {{ $activation->park->reference ?? '—' }}
                            </span>
                            <span class="text-gray-900 font-semibold">
                                {{ $activation->park?->getLocalizedName(app()->getLocale()) }}
                            </span>
                            @if ($activation->status === 'approved')
                                <span
                                    class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full font-semibold">✓ Одобрена</span>
                            @elseif ($activation->status === 'rejected')
                                <span
                                    class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full font-semibold">✕ Отклонена</span>
                            @else
                                <span
                                    class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full font-semibold">⏳ На рассмотрении</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 mt-2 flex flex-wrap gap-4">
                            <span>📅 {{ $activation->activation_date->format('d.m.Y') }}</span>
                            <span>📡 {{ $activation->qso_count }} QSO</span>
                            <span class="font-mono">{{ $activation->callsign }}</span>
                            @if ($activation->photos_count)
                                <span>📷 {{ $activation->photos_count }}</span>
                            @endif
                        </div>
                        @if ($activation->status === 'rejected' && $activation->moderator_note)
                            <p class="text-sm text-red-700 bg-red-50 rounded-lg px-3 py-2 mt-3">
                                <span class="font-semibold">Причина отклонения:</span>
                                {{ $activation->moderator_note }}
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('activations.show', $activation) }}"
                        class="text-sm font-semibold text-[--color-primary-600] hover:underline whitespace-nowrap">
                        Открыть →
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-500">
                <p class="text-lg font-semibold mb-2">Активаций пока нет</p>
                <p class="text-sm mb-6">Загрузите свой первый лог из парка — он появится здесь со статусом модерации.</p>
                <a href="{{ route('activations.create') }}"
                    class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-5 py-3 rounded-lg transition">
                    ➕ Загрузить активацию
                </a>
            </div>
        @endforelse
    </main>
</body>

</html>
