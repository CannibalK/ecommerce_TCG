# Imagen base: PHP 8.3 con FPM (FastCGI Process Manager, el modo recomendado para producción)
# "alpine" es una versión ultra liviana de Linux (~5MB vs ~150MB de Debian)
FROM php:8.4-cli-alpine

# Instala extensiones de PHP que Laravel necesita
# pdo_mysql → conectarse a MariaDB/MySQL
# mbstring → manejo de strings UTF-8 (cartas en japonés, etc.)
# zip/exif/bcmath → requeridas por varios paquetes de Laravel
RUN apk add --no-cache \
    curl \
    libxml2-dev \
    oniguruma-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath

# Composer es el gestor de paquetes de PHP (equivalente a npm para Node)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# En desarrollo, el código viene del volume mount (no se copia aquí).
# El contenedor solo provee el entorno PHP con las extensiones correctas.

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
