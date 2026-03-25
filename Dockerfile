FROM --platform=linux/amd64 php:8.1-apache

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Permitir .htaccess
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Crear .htaccess para routing API
RUN echo 'RewriteEngine On\nRewriteCond %{REQUEST_URI} ^/api/\nRewriteRule ^api/(.*)$ Back/index.php [QSA,L]\nOptions -Indexes' > /var/www/html/.htaccess

# Cambiar DocumentRoot a Front
RUN sed -i 's|/var/www/html|/var/www/html/Front|g' /etc/apache2/sites-available/000-default.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]