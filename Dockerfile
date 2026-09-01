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

CMD ["sh", "-c", "\
    until php bin/console doctrine:query:sql 'SELECT 1' > /dev/null 2>&1; do \
    echo 'Esperando a la base de datos...'; \
    sleep 2; \
    done; \
    php bin/console doctrine:database:create --if-not-exists --no-interaction && \
    php bin/console doctrine:migrations:migrate --no-interaction && \
    php bin/console doctrine:fixtures:load --no-interaction --append && \
    php bin/console lexik:jwt:generate-keypair --skip-if-exists && \
    php-fpm \
    "]

# --- 3. Etapa de Producción (Prod) ---
FROM base AS prod

# Copiamos el código
COPY . .

# Dependencias optimizadas para producción
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative

# Caché prod y permisos
RUN php bin/console cache:clear --env=prod \
    && chown -R www-data:www-data /var/www/html/var

CMD ["sh", "-c", "\
    until php bin/console doctrine:query:sql 'SELECT 1' > /dev/null 2>&1; do \
    echo 'Esperando a la base de datos...'; \
    sleep 2; \
    done; \
    php bin/console doctrine:database:create --if-not-exists --no-interaction && \
    php bin/console doctrine:migrations:migrate --no-interaction && \
    php bin/console lexik:jwt:generate-keypair --skip-if-exists && \
    php-fpm \
    "]