# Urban Parks — PHP-FPM образ для разработки и продакшена
FROM php:8.4-fpm

# Системные зависимости
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP-расширения: MySQL, intl (Filament), gd (фото-пруфы), zip, opcache
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        gd \
        bcmath \
        exif \
        opcache

# Composer из официального образа
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Пользователь с UID 1000, чтобы файлы на хосте не становились root
RUN useradd -G www-data -u 1000 -d /home/appuser appuser \
    && mkdir -p /home/appuser/.composer \
    && chown -R appuser:appuser /home/appuser

WORKDIR /var/www/html

# Предсоздаём vendor с владельцем appuser: docker-том, монтируемый сюда,
# унаследует эти права (иначе он создаётся от root и composer не может писать)
RUN mkdir -p /var/www/html/vendor && chown -R appuser:appuser /var/www/html \
    # и сразу гасим предупреждение git про "dubious ownership" смонтированного кода
    && su appuser -c "git config --global --add safe.directory /var/www/html"

USER appuser
