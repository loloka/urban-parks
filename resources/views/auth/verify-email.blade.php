<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтвердите email — Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                <div
                    class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center group-hover:bg-blue-700 transition">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-900">Urban Parks</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-red-600 transition">Выйти</button>
            </form>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">📬</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Подтвердите email</h1>
                <p class="text-gray-600 mb-6">
                    Мы отправили ссылку для подтверждения на
                    <span class="font-semibold text-gray-900">{{ auth()->user()->email }}</span>.
                    Перейдите по ней, чтобы загружать активации.
                </p>

                @if (session('success'))
                    <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @error('email')
                    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm">
                        {{ $message }}
                    </div>
                @enderror

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        Отправить письмо повторно
                    </button>
                </form>

                <a href="{{ route('cabinet') }}"
                    class="inline-block mt-4 text-sm text-gray-500 hover:text-gray-700">Перейти в кабинет →</a>
            </div>
        </div>
    </main>
</body>

</html>
