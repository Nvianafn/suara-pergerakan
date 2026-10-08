FROM php:8.4-fpm-bookworm AS backend
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor ca-certificates curl gnupg unzip unixodbc-dev \
    libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list -o /etc/apt/sources.list.d/microsoft-prod.list \
    && apt-get update && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd intl zip bcmath opcache \
    && pecl install sqlsrv-5.13.3 pdo_sqlsrv-5.13.3 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

FROM node:22-bookworm-slim AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY app/Livewire ./app/Livewire
COPY --from=backend /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

FROM backend AS runtime
COPY --from=frontend /build/public/build ./public/build
COPY docker/production/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/production/php.ini /usr/local/etc/php/conf.d/production.ini
COPY docker/production/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint
EXPOSE 8080
ENTRYPOINT ["app-entrypoint"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/app.conf"]
