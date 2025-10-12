# Imagen base con PHP 8.2 + Apache
FROM php:8.2-apache

# Instala extensiones necesarias para PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia el proyecto dentro del contenedor
COPY . /var/www/html/

# Usa la carpeta raíz como DocumentRoot (NO cambies a /public)
WORKDIR /var/www/html

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expone el puerto web
EXPOSE 80

# Inicia Apache
CMD ["apache2-foreground"]

