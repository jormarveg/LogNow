FROM php:8.2-fpm
RUN docker-php-ext-install pdo pdo_mysql
RUN printf "clear_env = no\n" >> /usr/local/etc/php-fpm.d/www.conf
