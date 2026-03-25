FROM php:8.1-apache

# Habilitar módulos necesarios
RUN a2enmod rewrite

# Instalar extensiones de PHP para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar el proyecto
COPY . /var/www/html/

# Configurar Apache: DocumentRoot y DirectoryIndex apuntando a Front/
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/Front|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/Front>|g' /etc/apache2/sites-available/000-default.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf

# Permitir .htaccess si lo necesitas después
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Permisos
RUN chgrp -R www-data /var/www/html && \
    chmod -R 775 /var/www/html

# Apache escucha en el puerto que Render asigne (o 80 por defecto)
ENV APACHE_PORT=80
EXPOSE 80

CMD ["apache2-foreground"]