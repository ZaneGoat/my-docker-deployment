FROM php:8.2-fpm

# Install PDO and MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli
