FROM php:8.1-apache

# Instalar extensión MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todo el proyecto
COPY . /var/www/html/

# Permisos
RUN chgrp -R www-data /var/www/html && \
    chmod -R 775 /var/www/html

EXPOSE 80
