FROM php:8.2-apache

# Extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activer mod_rewrite Apache
RUN a2enmod rewrite

# Configuration Apache — DocumentRoot vers public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Dossier uploads hors du webroot (sécurité)
RUN mkdir -p /var/www/storage/uploads && \
    chown -R www-data:www-data /var/www/storage

# Configuration PHP
RUN echo "upload_max_filesize = 5M\npost_max_size = 5M\nmax_execution_time = 30" > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

EXPOSE 80
