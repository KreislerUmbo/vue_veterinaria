# Stage 1: Build frontend assets (Vue 3)
FROM node:18 as frontend-builder

WORKDIR /app

# Copia solo los archivos necesarios para instalar dependencias
COPY package*.json ./
COPY vite.config.js ./

# Instala dependencias y construye los assets
RUN npm install && npm run build

# Stage 2: Build backend (Laravel 12)
FROM composer:2 as backend-builder

WORKDIR /app

# Copia archivos necesarios para composer
COPY composer.json composer.lock ./

# Instala dependencias de PHP (sin las de desarrollo)
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

# Stage 3: Runtime environment
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Instala dependencias del sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    gd \
    zip \
    opcache

# Configuración de PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configuración de Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/veterinaria.conf /etc/nginx/conf.d/default.conf

# Configuración de Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copia la aplicación
COPY . .
COPY --from=frontend-builder /app/public/build ./public/build
COPY --from=backend-builder /app/vendor ./vendor

# Configura permisos
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Puerto expuesto
EXPOSE 8080

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]