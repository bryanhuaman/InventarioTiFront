# Imagen base con PHP 8.2 + Apache
FROM php:8.2-apache

# Instala extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia tu proyecto
COPY . /var/www/html/

# Cambia el DocumentRoot a public/
WORKDIR /var/www/html
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 🔥 Agrega esta línea para que Apache use el puerto dinámico
RUN echo "Listen ${PORT}" >> /etc/apache2/ports.conf

# Expón el puerto dinámico
EXPOSE ${PORT}

# Inicia Apache
CMD ["apache2-foreground"]
