<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.rules_page.title') }} - Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="bg-white shadow-sm">
        <div class="container mx-auto flex items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-primary-600 hover:text-primary-700">Urban Parks</a>
            <div class="flex items-center gap-2">
                <a href="?lang=ru"
                    class="rounded px-2 py-1 {{ app()->getLocale() === 'ru' ? 'bg-blue-100 font-bold text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">RU</a>
                <a href="?lang=en"
                    class="rounded px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-blue-100 font-bold text-blue-800' : 'text-gray-600 hover:bg-gray-100' }}">EN</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto flex min-h-[calc(100vh-73px)] items-center px-4 py-16">
        <section class="mx-auto max-w-2xl rounded-2xl bg-white p-8 text-center shadow-sm md:p-12">
            <div class="mb-5 text-5xl">📋</div>
            <h1 class="mb-4 text-3xl font-bold md:text-4xl">{{ __('ui.rules_page.title') }}</h1>
            <p class="text-lg text-gray-600">{{ __('ui.rules_page.coming_soon') }}</p>
            <a href="{{ route('home') }}"
                class="mt-8 inline-flex items-center gap-2 rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-primary-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-primary-200">
                <span aria-hidden="true">←</span>
                <span>{{ __('ui.rules_page.back_to_home') }}</span>
            </a>
        </section>
    </main>
</body>

</html>
