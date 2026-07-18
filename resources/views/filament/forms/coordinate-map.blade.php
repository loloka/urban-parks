{{-- Интерактивный выбор координат парка: перетаскиваемый маркер + клик по карте.
     Стек: MapLibre GL JS + OpenFreeMap (как на публичной части).

     Компонент — самодостаточный inline x-data: не зависит от отдельного <script>,
     поэтому корректно инициализируется и в модалке «Просмотр» (AJAX-контент),
     где инлайновые <script> не выполняются. --}}
@php
    // Начальный центр берём из самой записи (надёжно и в модалке, где путь
    // стейта Livewire отличается от страницы редактирования).
    $mapRecord = $getRecord();
    $mapInitLat = $mapRecord?->latitude;
    $mapInitLng = $mapRecord?->longitude;
@endphp
<div wire:ignore class="space-y-2" x-data="{
    map: null,
    marker: null,
    cfgLat: {{ $mapInitLat !== null ? $mapInitLat : 'null' }},
    cfgLng: {{ $mapInitLng !== null ? $mapInitLng : 'null' }},
    editable: {{ (isset($isDisabled) && $isDisabled()) ? 'false' : 'true' }},

    boot() {
        this.ensureLib().then(() => this.initMap());
    },

    ensureLib() {
        return new Promise((resolve) => {
            if (window.maplibregl) return resolve();
            if (!document.getElementById('maplibre-css')) {
                const link = document.createElement('link');
                link.id = 'maplibre-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css';
                document.head.appendChild(link);
            }
            let s = document.getElementById('maplibre-js');
            if (!s) {
                s = document.createElement('script');
                s.id = 'maplibre-js';
                s.src = 'https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js';
                s.onload = () => resolve();
                document.head.appendChild(s);
            } else if (window.maplibregl) {
                resolve();
            } else {
                s.addEventListener('load', () => resolve());
            }
        });
    },

    readLatLng() {
        let lat = parseFloat(this.$wire.get('data.latitude'));
        let lng = parseFloat(this.$wire.get('data.longitude'));
        if (Number.isNaN(lat) && this.cfgLat != null) lat = parseFloat(this.cfgLat);
        if (Number.isNaN(lng) && this.cfgLng != null) lng = parseFloat(this.cfgLng);
        return {
            lat: Number.isNaN(lat) ? 55.0335 : lat,
            lng: Number.isNaN(lng) ? 82.9285 : lng,
            empty: Number.isNaN(lat) || Number.isNaN(lng),
        };
    },

    writeLatLng(lat, lng) {
        if (!this.editable) return; // в режиме «Просмотр» писать некуда
        try {
            this.$wire.set('data.latitude', Number(lat.toFixed(7)), false);
            this.$wire.set('data.longitude', Number(lng.toFixed(7)), false);
        } catch (e) {}
    },

    initMap() {
        const c = this.readLatLng();
        this.map = new maplibregl.Map({
            container: this.$refs.map,
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [c.lng, c.lat],
            zoom: c.empty ? 10 : 14,
            attributionControl: { compact: true },
        });
        this.map.addControl(new maplibregl.NavigationControl());
        this.map.on('load', () => this.map.resize());

        // Контейнер в модалке сначала скрыт (0px) — перерисуем, когда получит размер.
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => { if (this.map) this.map.resize(); });
            ro.observe(this.$refs.map);
        }
        setTimeout(() => this.map && this.map.resize(), 300);
        setTimeout(() => this.map && this.map.resize(), 800);

        this.marker = new maplibregl.Marker({ draggable: this.editable, color: '#d97706' })
            .setLngLat([c.lng, c.lat])
            .addTo(this.map);

        // Интерактив и синхронизация с полями — только в режиме редактирования
        if (this.editable) {
            this.marker.on('dragend', () => {
                const p = this.marker.getLngLat();
                this.writeLatLng(p.lat, p.lng);
            });

            this.map.on('click', (e) => {
                this.marker.setLngLat(e.lngLat);
                this.writeLatLng(e.lngLat.lat, e.lngLat.lng);
            });

            const sync = () => {
                const lat = parseFloat(this.$wire.get('data.latitude'));
                const lng = parseFloat(this.$wire.get('data.longitude'));
                if (Number.isNaN(lat) || Number.isNaN(lng)) return;
                const cur = this.marker.getLngLat();
                if (Math.abs(cur.lat - lat) > 1e-6 || Math.abs(cur.lng - lng) > 1e-6) {
                    this.marker.setLngLat([lng, lat]);
                    this.map.easeTo({ center: [lng, lat] });
                }
            };
            try {
                this.$wire.$watch('data.latitude', sync);
                this.$wire.$watch('data.longitude', sync);
            } catch (e) {}
        }
    },
}" x-init="boot()">
    <div x-ref="map" class="w-full rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
        style="height: 360px;"></div>
    <p x-show="editable" class="text-sm text-gray-500 dark:text-gray-400">
        Перетащите маркер или кликните по карте, чтобы задать координаты. Значения полей ниже обновятся автоматически.
    </p>
</div>
