FROM --platform=linux/amd64 php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar TODO el proyecto
COPY . /var/www/html/

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf

# Configurar VirtualHost COMPLETO
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/Front\n\
    \n\
    <Directory /var/www/html/Front>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.php\n\
    </Directory>\n\
    \n\
    Alias /api /var/www/html/Back\n\
    Alias /Back /var/www/html/Back\n\
    \n\
    <Directory /var/www/html/Back>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.php\n\
    </Directory>\n\
    \n\
    RewriteEngine On\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Permisos (usuario CORRECTO: www-data)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]