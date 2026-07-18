<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
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
                <span class="text-xl font-bold text-gray-900 group-hover:text-[--color-primary-600] transition">Urban
                    Parks</span>
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Регистрация активатора</h1>
                <p class="text-gray-500 text-sm mb-6">Создайте аккаунт, чтобы загружать активации под своим позывным.</p>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Имя</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full border-gray-300 rounded-lg focus:ring-[--color-primary-600] focus:border-[--color-primary-600]">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Позывной</label>
                        <input type="text" name="callsign" value="{{ old('callsign') }}" required placeholder="R9OGL"
                            class="w-full border-gray-300 rounded-lg uppercase focus:ring-[--color-primary-600] focus:border-[--color-primary-600]">
                        <p class="text-xs text-gray-500 mt-1">Ваш основной позывной. Будет подставляться при загрузке.</p>
                        @error('callsign')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full border-gray-300 rounded-lg focus:ring-[--color-primary-600] focus:border-[--color-primary-600]">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Пароль</label>
                        <input type="password" name="password" required
                            class="w-full border-gray-300 rounded-lg focus:ring-[--color-primary-600] focus:border-[--color-primary-600]">
                        <p class="text-xs text-gray-500 mt-1">Минимум 8 символов.</p>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Повторите пароль</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full border-gray-300 rounded-lg focus:ring-[--color-primary-600] focus:border-[--color-primary-600]">
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                        Зарегистрироваться
                    </button>
                </form>

                <p class="text-sm text-gray-600 mt-6 text-center">
                    Уже есть аккаунт?
                    <a href="{{ route('login') }}"
                        class="text-[--color-primary-600] font-semibold hover:underline">Войти</a>
                </p>
            </div>
        </div>
    </main>
</body>

</html>
