# Dockerfile para Symfony con pdo_mysql (TFG / demo)
FROM php:8.2-apache

# variables para composer en build
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
     libonig-dev libzip-dev libicu-dev libpng-dev libjpeg-dev libfreetype6-dev \
     default-mysql-client unzip git zip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql intl zip opcache gd \
  && a2enmod rewrite \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# instalar Composer (desde la imagen oficial de composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copiar composer files e instalar dependencias en un layer cacheable
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# copiar el resto del proyecto
COPY . .

# permisos básicos (si usas var/cache y var/log)
RUN chown -R www-data:www-data var || true \
  && chmod -R 775 var || true

EXPOSE 80

# arrancar Apache (servirá public/index.php)
CMD ["apache2-foreground"]
