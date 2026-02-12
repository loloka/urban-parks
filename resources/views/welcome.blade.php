<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urban Parks - Международная программа активации городских парков</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
</head>
<body class="bg-gray-50">
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-[--color-primary-600] rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Urban Parks</h1>
                        <p class="text-sm text-gray-600">Городские парки для радиолюбителей</p>
                    </div>
                </div>
                <nav class="hidden md:flex space-x-6">
                    <a href="#map" class="text-gray-700 hover:text-[--color-primary-600] transition">Карта</a>
                    <a href="#parks" class="text-gray-700 hover:text-[--color-primary-600] transition">Парки</a>
                    <a href="#" class="text-gray-700 hover:text-[--color-primary-600] transition">Активации</a>
                    <a href="#" class="text-gray-700 hover:text-[--color-primary-600] transition">Дипломы</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                🎯 Активируй городские парки!
            </h2>
            <p class="text-xl md:text-2xl text-blue-100 mb-8">
                Международная радиолюбительская программа для работы из городских парков 🌍
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold" id="parks-count">{{ $stats['total_parks'] }}</div>
                    <div class="text-sm text-blue-200">Парков</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['total_activations'] }}</div>
                    <div class="text-sm text-blue-200">Активаций</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['cities'] }}</div>
                    <div class="text-sm text-blue-200">Городов</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-lg min-w-[140px]">
                    <div class="text-3xl font-bold">{{ $stats['regions'] }}</div>
                    <div class="text-sm text-blue-200">Регионов</div>
                </div>
            </div>
            
            <!-- Featured City Banner -->
            <div class="mt-8 inline-block bg-yellow-400 text-gray-900 px-6 py-3 rounded-full font-bold">
                🏆 Столица программы: Новосибирск (6 парков)
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-12" id="map">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-gray-900">🗺️ Карта парков</h3>
                    <div class="flex flex-wrap gap-2">
                        <select id="city-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--color-primary-600] focus:border-transparent">
                            <option value="">Все города</option>
                        </select>
                        <select id="region-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--color-primary-600] focus:border-transparent">
                            <option value="">Все регионы</option>
                        </select>
                        <input 
                            type="text" 
                            id="search-input" 
                            placeholder="🔍 Поиск парка..." 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--color-primary-600] focus:border-transparent"
                        />
                    </div>
                </div>
                
                <div id="map" class="rounded-xl"></div>
                
                <div class="mt-4 text-sm text-gray-600 flex items-center justify-between">
                    <div>
                        <span class="font-semibold" id="visible-parks-count">0</span> парков на карте
                    </div>
                    <button id="reset-filters" class="text-[--color-primary-600] hover:underline">
                        Сбросить фильтры
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Parks List -->
    <section class="py-12 bg-gray-50" id="parks">
        <div class="container mx-auto px-4">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">📍 Последние парки</h3>
            <div id="parks-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Динамически загружается через JS -->
                <div class="col-span-full text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[--color-primary-600]"></div>
                    <p class="mt-4 text-gray-600">Загрузка парков...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h3 class="text-3xl font-bold text-center text-gray-900 mb-12">Почему Urban Parks?</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Доступность</h4>
                    <p class="text-gray-600">Городские парки доступны круглый год и находятся рядом с домом</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Дипломы</h4>
                    <p class="text-gray-600">Зарабатывай дипломы за активацию парков и связи с активаторами</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Сообщество</h4>
                    <p class="text-gray-600">Встречай единомышленников и участвуй в массовых активациях</p>
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
                    <p class="text-gray-400">Международная радиолюбительская программа для работы из городских парков</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Навигация</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">О проекте</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Правила</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Дипломы</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">API</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Контакты</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📧 info@urbanparks.ru</li>
                        <li>📱 Telegram: @urbanparks</li>
                        <li>🌐 VK: vk.com/urbanparks</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 text-center text-gray-400">
                <p>© 2026 Urban Parks. Сделано с ❤️ радиолюбителями для радиолюбителей</p>
            </div>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    
    <script>
        // Инициализация карты
        // Исправлено: убираем атрибуцию с флагом
        const map = L.map('map', {
            attributionControl: false  // Отключаем стандартную атрибуцию
        }).setView([55.751244, 82.914517], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        // Добавляем свою нейтральную атрибуцию внизу справа
        L.control.attribution({
            position: 'bottomright',
            prefix: false  // Убираем "Leaflet"
        }).addAttribution('© OpenStreetMap').addTo(map);

        // Кластеризация маркеров
        const markers = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });

        let allParks = [];
        let filteredParks = [];

        // Иконка маркера
        const customIcon = L.divIcon({
            className: 'custom-marker',
            iconSize: [30, 30],
            html: '<div style="background:#2563eb;border:3px solid white;border-radius:50%;width:30px;height:30px;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>'
        });

        // Загружаем парки
        fetch('/api/parks')
            .then(response => response.json())
            .then(parks => {
                allParks = parks;
                filteredParks = parks;
                updateMap(parks);
                renderParksList(parks.slice(0, 6)); // Показываем первые 6
            })
            .catch(error => {
                console.error('Ошибка загрузки парков:', error);
                document.getElementById('parks-list').innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-600">
                        ❌ Ошибка загрузки данных
                    </div>
                `;
            });

        // Загружаем города для фильтра
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

        // Загружаем регионы для фильтра
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
            markers.clearLayers();
            
            parks.forEach(park => {
                const marker = L.marker([park.latitude, park.longitude], { icon: customIcon })
                    .bindPopup(`
                        <div class="p-2 min-w-[200px]">
                            <h4 class="font-bold text-lg mb-1">${park.name}</h4>
                            <p class="text-sm text-gray-600 font-mono mb-1">${park.reference}</p>
                                                        <p class="text-sm mb-1">📍 ${park.city}, ${park.region}</p>
                            ${park.area ? `<p class="text-xs text-gray-500">Площадь: ${park.area}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-2">⚡ Активаций: ${park.activation_count}</p>
                            <a href="/park/${park.id}" class="text-blue-600 text-sm hover:underline mt-2 inline-block">Подробнее →</a>
                        </div>
                    `);
                markers.addLayer(marker);
            });

            map.addLayer(markers);
            document.getElementById('visible-parks-count').textContent = parks.length;
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
                        <p class="text-lg font-semibold">Парки не найдены</p>
                        <p class="text-sm mt-2">Попробуйте изменить фильтры</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = parks.map(park => `
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
                            ${park.activation_count}
                        </span>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">${park.name}</h4>
                    <p class="text-sm text-gray-600 mb-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        ${park.city}, ${park.region}
                    </p>
                    ${park.area ? `<p class="text-xs text-gray-500 mb-3">📐 ${park.area}</p>` : ''}
                    <p class="text-sm text-gray-700 mb-4 line-clamp-2">${park.description || 'Городской парк для активаций радиолюбителей'}</p>
                    <a href="/park/${park.id}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold text-sm transition">
                        Подробнее
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            `).join('');
        }

        // Фильтрация
        function applyFilters() {
            const cityFilter = document.getElementById('city-filter').value;
            const regionFilter = document.getElementById('region-filter').value;
            const searchQuery = document.getElementById('search-input').value.toLowerCase();

            filteredParks = allParks.filter(park => {
                const matchCity = !cityFilter || park.city === cityFilter;
                const matchRegion = !regionFilter || park.region === regionFilter;
                const matchSearch = !searchQuery || 
                    park.name.toLowerCase().includes(searchQuery) ||
                    park.reference.toLowerCase().includes(searchQuery) ||
                    park.city.toLowerCase().includes(searchQuery);

                return matchCity && matchRegion && matchSearch;
            });

            updateMap(filteredParks);
            renderParksList(filteredParks.slice(0, 6));
        }

        // События фильтров
        document.getElementById('city-filter').addEventListener('change', applyFilters);
        document.getElementById('region-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('input', applyFilters);

        // Сброс фильтров
        document.getElementById('reset-filters').addEventListener('click', () => {
            document.getElementById('city-filter').value = '';
            document.getElementById('region-filter').value = '';
            document.getElementById('search-input').value = '';
            applyFilters();
        });
    </script>

</body>
</html>