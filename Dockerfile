FROM php:8.2-fpm
RUN docker-php-ext-install pdo pdo_mysql
RUN printf "clear_env = no\n" >> /usr/local/etc/php-fpm.d/www.conf
RUN printf "upload_max_filesize = 10M\npost_max_size = 10M\n" > /usr/local/etc/php/conf.d/uploads.ini
