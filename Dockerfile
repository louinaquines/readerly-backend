FROM php:8.2-cli

# Force rebuild: v2
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql curl zip bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 777 storage bootstrap/cache

CMD php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}