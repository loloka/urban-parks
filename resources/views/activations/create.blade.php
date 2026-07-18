<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузить активацию - Urban Parks</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto py-12 px-4">
        <!-- Хедер -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <a href="/" class="text-blue-600 hover:underline">← На главную</a>
                <a href="{{ route('cabinet') }}" class="text-blue-600 hover:underline">👤 Мой кабинет</a>
            </div>
            <h1 class="text-4xl font-bold mt-4">📡 Загрузить активацию</h1>
            <p class="text-gray-600 mt-2">
                Прикрепите ADIF-лог и скриншот из QTHnow. Активация будет проверена модератором.
            </p>
        </div>

        <!-- Успешное сообщение -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Предупреждения импорта -->
        @if (session('warnings') && count(session('warnings')) > 0)
            <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-6 text-sm">
                <p class="font-semibold mb-1">⚠ Обратите внимание:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach (session('warnings') as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Форма -->
        <form method="POST" action="{{ route('activations.store') }}" enctype="multipart/form-data"
            class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <!-- Парк -->
            <div>
                <label class="block font-semibold mb-2">Парк *</label>
                <select name="park_id" required
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Выберите парк</option>
                    @foreach ($parks as $park)
                        <option value="{{ $park->id }}" {{ old('park_id') == $park->id ? 'selected' : '' }}>
                            {{ $park->reference }} — {{ $park->name }} ({{ $park->city }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Парк выбирается здесь — спец-теги (MY_SIG) в логе не обязательны
                </p>
                @error('park_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Позывной -->
            <div>
                <label class="block font-semibold mb-2">Позывной *</label>
                <input type="text" name="callsign" value="{{ old('callsign', $user?->callsign) }}"
                    placeholder="R9OGL" required
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 uppercase">
                <p class="text-xs text-gray-500 mt-1">
                    Подставлен из профиля — измените, если работали спец-позывным или с /P.
                </p>
                @error('callsign')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- ADIF-лог -->
            <div>
                <label class="block font-semibold mb-2">ADIF-лог (.adi) *</label>
                <input type="file" name="adif" accept=".adi,.adif,.txt" required
                    class="w-full border border-gray-300 rounded-lg p-2 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">
                    Экспорт из любого логгера: RUMlogNG, UR5EQF, Log4OM, HAMRS... Дата и число QSO возьмутся из лога.
                </p>
                @error('adif')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Скриншот QTHnow -->
            <div>
                <label class="block font-semibold mb-2">Скриншот QTHnow *</label>
                <input type="file" name="screenshot" accept="image/jpeg,image/png,image/webp" required
                    class="w-full border border-gray-300 rounded-lg p-2 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">
                    Обязательный пруф присутствия: скриншот с вашим позывным и координатами из парка
                </p>
                @error('screenshot')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Фото (опционально) -->
            <div>
                <label class="block font-semibold mb-2">Фото с активации (по желанию)</label>
                <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple
                    class="w-full border border-gray-300 rounded-lg p-2 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 file:font-semibold hover:file:bg-gray-200">
                <p class="text-xs text-gray-500 mt-1">
                    До 5 фото: аппаратура, антенна, виды парка — станут историей активации
                </p>
                @error('photos')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('photos.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Заметки -->
            <div>
                <label class="block font-semibold mb-2">Заметки (опционально)</label>
                <textarea name="notes" rows="3" placeholder="Условия, антенна, особенности активации..."
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
                <li>• Скриншот QTHnow с позывным и координатами обязателен</li>
                <li>• Запрещены ретрансляторы</li>
                <li>• Одна активация парка в день (один лог = один день)</li>
            </ul>
        </div>
    </div>
</body>

</html>
