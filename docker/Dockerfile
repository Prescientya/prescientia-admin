FROM dunglas/frankenphp

# install memcached extension
RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev \
    && pecl install memcached


# Install required PHP extensions (GD and PCNTL)
RUN install-php-extensions \
    gd \
    pcntl intl \
    pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath opcache \
    zip gmp

RUN apt-get update && apt-get install -y supervisor && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

ENV SERVER_NAME=:80

# Change APP_ENV and APP_DEBUG to be production ready
#RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

# Install Composer manually
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# check if folder vendor is exist? if exist deleted
RUN [ -d "vendor" ] && rm -rf vendor || echo "Vendor folder does not exist, skipping delete step"

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

RUN php artisan octane:install --server=frankenphp

# storage link
RUN php artisan storage:link

# Copy supervisord configuration
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf


# Expose port 80
EXPOSE 80

# start supervisord
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
