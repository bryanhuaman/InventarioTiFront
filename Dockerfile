# Imagen base con PHP 8.2 + Apache
FROM php:8.2-apache

# Instala extensiones necesarias para PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia el proyecto dentro del contenedor
COPY . /var/www/html/

# Cambia el DOCUMENT_ROOT de Apache a "public/"
WORKDIR /var/www/html
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expone el puerto web
EXPOSE 80

# Inicia Apache
CMD ["apache2-foreground"]
