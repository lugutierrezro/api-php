FROM php:8.1-apache

# Copia todos los archivos al directorio del servidor web de Apache
COPY . /var/www/html/

# Instala las extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql