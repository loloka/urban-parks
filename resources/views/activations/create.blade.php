<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить активацию - Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto py-12 px-4">
        <!-- Хедер -->
        <div class="mb-8">
            <a href="/" class="text-blue-600 hover:underline">← На главную</a>
            <h1 class="text-4xl font-bold mt-4">📡 Добавить активацию</h1>
            <p class="text-gray-600 mt-2">
                Заполните форму ниже. Активация будет проверена модератором.
            </p>
        </div>

        <!-- Успешное сообщение -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Форма -->
        <form method="POST" action="{{ route('activations.store') }}" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <!-- Парк -->
            <div>
                <label class="block font-semibold mb-2">Парк *</label>
                <select name="park_id" required
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Выберите парк</option>
                    @foreach ($parks as $park)
                        <option value="{{ $park->id }}" {{ old('park_id') == $park->id ? 'selected' : '' }}>
                            {{ $park->code }} - {{ $park->name }} ({{ $park->city }})
                        </option>
                    @endforeach
                </select>
                @error('park_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Позывной -->
            <div>
                <label class="block font-semibold mb-2">Позывной *</label>
                <input type="text" name="callsign" value="{{ old('callsign') }}" placeholder="R9OGL" required
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 uppercase">
                @error('callsign')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Дата -->
            <div>
                <label class="block font-semibold mb-2">Дата активации *</label>
                <input type="date" name="activation_date" value="{{ old('activation_date', date('Y-m-d')) }}"
                    max="{{ date('Y-m-d') }}" required
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('activation_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Количество QSO -->
            <div>
                <label class="block font-semibold mb-2">Количество QSO *</label>
                <input type="number" name="qso_count" value="{{ old('qso_count') }}" min="1" max="9999"
                    required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @error('qso_count')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Заметки -->
            <div>
                <label class="block font-semibold mb-2">Заметки (опционально)</label>
                <textarea name="notes" rows="4" placeholder="Диапазоны, особенности активации..."
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Кнопка -->
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                📤 Отправить на модерацию
            </button>
        </form>

        <!-- Правила -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="font-bold mb-2">📋 Правила активации</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• Минимум 10 QSO для зачёта активации</li>
                <li>• Работа должна вестись с территории парка</li>
                <li>• Запрещены ретрансляторы</li>
                <li>• Одна активация парка в день</li>
            </ul>
        </div>
    </div>
</body>

</html>
