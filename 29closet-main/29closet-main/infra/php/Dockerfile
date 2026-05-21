FROM php:8.1-fpm

ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

RUN apt-get update && \
    apt-get install -y libicu-dev && \
    docker-php-ext-install intl && \
    rm -rf /var/lib/apt/lists/*

RUN apt-get update && \
    apt-get install -y libzip-dev libicu-dev unzip && \
    docker-php-ext-install zip intl && \
    rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 thk_user \
 && useradd -m -u 1000 -g 1000 -s /bin/bash thk_user

WORKDIR /var/www/html/app

RUN chown -R thk_user:thk_user ./

USER thk_user

EXPOSE 9000
