FROM php:8.1-apache

# Instalar extensión de MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Dar permisos
RUN chgrp -R www-data /var/www/html && \
    chmod -R 775 /var/www/html

EXPOSE 80
