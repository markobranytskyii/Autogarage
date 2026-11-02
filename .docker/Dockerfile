FROM php:8.5-apache

RUN docker-php-ext-install pdo pdo_mysql

# .htaccess toestaan (voor de eigen 404-pagina)
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
