FROM php:8.1-apache

# Instalar dependencias del sistema y habilitar PDO SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Habilitar el módulo rewrite de Apache para la reescritura de URLs (.htaccess)
RUN a2enmod rewrite

# Reconfigurar Apache para que apunte al directorio /public de forma segura
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar el código del proyecto al directorio de trabajo
COPY . /var/www/html

# Crear directorios para base de datos/sesiones y otorgar permisos de escritura a www-data (Apache)
RUN mkdir -p /var/www/html/almacenamiento/sesiones \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/almacenamiento

# Exponer el puerto 80
EXPOSE 80

# Definir variables de entorno por defecto
ENV FASTPLAY_ENTORNO=produccion
ENV FASTPLAY_BD_PATH=/var/www/html/almacenamiento/fastplay.sqlite
ENV FASTPLAY_SESIONES_PATH=/var/www/html/almacenamiento/sesiones
