FROM php:8.2-apache

# Instalamos librerías necesarias para mysqli y curl
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql curl \
    && docker-php-ext-enable mysqli curl

# Copiamos todo el proyecto al contenedor
COPY . /var/www/html/

# Cambiamos permisos completos para desarrollo / prueba
RUN chmod -R 777 /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Habilitamos mod_rewrite
RUN a2enmod rewrite

# Configuramos Apache para permitir override (por si usas .htaccess)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Exponemos el puerto 80
EXPOSE 80
