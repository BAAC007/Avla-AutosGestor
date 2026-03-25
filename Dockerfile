FROM --platform=linux/amd64 php:8.1-apache

# Habilitar módulos de Apache
RUN a2enmod rewrite headers

# Instalar extensiones PHP para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar proyecto
COPY . /var/www/html/

# Configurar Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    # DocumentRoot apunta al Front (UI principal)
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/Front|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/Front>|g' /etc/apache2/sites-available/000-default.conf && \
    # Permitir .htaccess y configurar DirectoryIndex
    sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf

# Configurar virtual host para routing de API
RUN echo '\
<VirtualHost *:80>\
    DocumentRoot /var/www/html/Front\
    <Directory /var/www/html/Front>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php\
    </Directory>\
    \
    # Routing: /api/* → Back/index.php\
    Alias /api /var/www/html/Back\
    <Directory /var/www/html/Back>\
        Options -Indexes +FollowSymLinks\
        AllowOverride All\
        Require all granted\
        DirectoryIndex index.php\
        # Headers para CORS (se pueden sobrescribir en PHP)\
        Header set Access-Control-Allow-Origin "*"\
        Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\
        Header set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"\
    </Directory>\
    \
    # Manejo de preflight OPTIONS\
    RewriteEngine On\
    RewriteCond %{REQUEST_METHOD} OPTIONS\
    RewriteRule ^(.*)$ $1 [R=200,L]\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Permisos
RUN chgrp -R www-data /var/www/html && chmod -R 775 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]