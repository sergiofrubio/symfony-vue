# --- 1. Etapa Base (Común para todos los entornos) ---
FROM php:8.4-fpm AS base

# Dependencias necesarias para Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- 2. Etapa de Desarrollo (Dev) ---
FROM base AS dev

# Copiamos el código y ejecutamos la instalación estándar
COPY . .
RUN composer install

CMD ["php-fpm"]

# --- 3. Etapa de Producción (Prod) ---
FROM base AS prod

# Copiamos el código
COPY . .

# Dependencias optimizadas para producción
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative

# Caché prod y permisos
RUN php bin/console cache:clear --env=prod \
    && chown -R www-data:www-data /var/www/html/var

CMD ["php-fpm"]