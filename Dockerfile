FROM --platform=linux/amd64 php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar TODO
COPY . /var/www/html/

# DEBUG: Ver qué se copió
RUN echo "=== FILES IN /var/www/html/ ===" && \
    ls -la /var/www/html/ && \
    echo "=== FILES IN /var/www/html/Back/ ===" && \
    (ls -la /var/www/html/Back/ 2>&1 || echo "ERROR: Back folder not found!") && \
    echo "=== FILES IN /var/www/html/Back/admin/ ===" && \
    (ls -la /var/www/html/Back/admin/ 2>&1 || echo "ERROR: Back/admin folder not found!")

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf

# Configurar VirtualHost SIMPLE (sin saltos de línea complejos)
RUN echo '<VirtualHost *:80>' > /etc/apache2/sites-available/000-default.conf && \
    echo '    DocumentRoot /var/www/html/Front' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    <Directory /var/www/html/Front>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Options -Indexes +FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        DirectoryIndex index.php' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Alias /api /var/www/html/Back' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    <Directory /var/www/html/Back>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Options -Indexes +FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        DirectoryIndex index.php' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Alias /admin /var/www/html/Back/admin' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    <Directory /var/www/html/Back/admin>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Options -Indexes +FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        DirectoryIndex index.php' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf

# Permisos MUY abiertos para debug
RUN chmod -R 777 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]