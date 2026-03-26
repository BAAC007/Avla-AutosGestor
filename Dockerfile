FROM --platform=linux/amd64 php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite y alias
RUN a2enmod rewrite alias

# Copiar TODO el proyecto
COPY . /var/www/html/

# Configurar Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Crear VirtualHost con todas las rutas correctas
RUN cat > /etc/apache2/sites-available/000-default.conf << 'VHOST'
<VirtualHost *:80>
    DocumentRoot /var/www/html/Front
    <Directory /var/www/html/Front>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    Alias /api /var/www/html/Back
    <Directory /var/www/html/Back>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    Alias /admin /var/www/html/Back/admin
    <Directory /var/www/html/Back/admin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    Alias /back-css /var/www/html/Back/css
    <Directory /var/www/html/Back/css>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
VHOST

RUN chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]