FROM php:7.4-fpm-alpine AS base

RUN apk add --no-cache ca-certificates autoconf git nginx curl \
    libpng-dev libxml2-dev libzip-dev libjpeg-turbo-dev zip unzip freetype-dev && \ 
    docker-php-ext-install zip soap pdo_mysql bcmath 
#mbstring already in fpm-alpine , remove for ext-install
#supervisor removed

RUN docker-php-ext-install gd

WORKDIR /var/www/html

COPY . /var/www/html


# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Update some configurations
RUN sed -ri -e 's!;date.timezone =!date.timezone = "America/Santiago"!g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/;max_input_vars = 1000/max_input_vars = 100000000/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/;memory_limit = 128M/memory_limit = 512M/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 512M/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/;max_execution_time = 30/max_execution_time = 600/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/max_execution_time = 30/max_execution_time = 600/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/;max_input_time = 60/max_input_time = 600/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/max_input_time = 60/max_input_time = 600/g' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/post_max_size = 8M/post_max_size = 256M/g' "$PHP_INI_DIR/php.ini" 

COPY docker/nginx.conf /etc/nginx/http.d/default.conf 
#COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

EXPOSE 80

#CMD ["/usr/bin/supervisord","-n","-c","/etc/supervisor/supervisord.conf"]

FROM base AS builder

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-interaction --prefer-dist --no-dev

RUN rm -rf /usr/local/bin/composer && rm -rf /root/.composer

FROM base AS production

COPY --from=builder /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html/storage 
#&& chmod -R 755 /var/www/html/bootstrap/cache -- only in laravel
