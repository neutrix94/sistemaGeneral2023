# syntax=docker/dockerfile:experimental

# PHP base image
FROM php:5.6-apache AS base

# Enable mod_rewrite module
RUN a2enmod rewrite

# Update apt source list (for debian stretch on old PS images using debian 9)
RUN if [ $(awk -F. '{print $1}' /etc/debian_version) -eq 9 ]; then \
  printf 'deb http://archive.debian.org/debian/ stretch main contrib non-free deb http://archive.debian.org/debian-security/ stretch/updates main contrib non-free deb http://archive.debian.org/debian/ stretch-backports main contrib non-free' > /etc/apt/sources.list; \
fi

# Install dependencies
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxpm-dev \
    libc-client2007e-dev \
    libkrb5-dev \
    libicu-dev \
    libmemcached-dev \
    default-libmysqlclient-dev \
    libpq-dev \
    libreadline-dev \
    libtidy-dev \
    libxml2-dev \
    libxslt-dev \
    libpspell-dev \
    libedit-dev \
    libsqlite3-dev \
    libenchant-dev \
    libldb-dev \
    libldap2-dev \
    libmcrypt-dev \
    libssh2-1-dev \
    libonig-dev \
    libkrb5-dev \
    libpspell-dev \
    libenchant-dev \
    libxpm-dev \
    libjpeg-dev \
    libcurl4-openssl-dev \
    libsqlite3-dev \
    libmemcached-dev \
    zlib1g-dev \
    libbz2-dev \
    wget \
    build-essential \
    m4 \
    libgmp3-dev \
  && rm -rf /var/lib/apt/lists/*

# Manually download and install GMP library
RUN mkdir -p /usr/local/src \
  && cd /usr/local/src \
  && wget https://ftp.gnu.org/gnu/gmp/gmp-6.2.1.tar.xz \
  && tar -xf gmp-6.2.1.tar.xz \
  && cd gmp-6.2.1 \
  && ./configure \
  && make \
  && make install \
  && cd .. \
  && rm -rf gmp-6.2.1 gmp-6.2.1.tar.xz

# Check if gmp.h is found after manual installation
RUN find / -name gmp.h

# Download and install igbinary version 2 manually
RUN mkdir -p /usr/src/php/ext/igbinary \
  && curl -fsSL -o igbinary.tar.gz https://github.com/igbinary/igbinary/archive/2.0.8.tar.gz \
  && tar -xzf igbinary.tar.gz --strip-components=1 -C /usr/src/php/ext/igbinary \
  && rm igbinary.tar.gz \
  && docker-php-ext-install igbinary \
  && docker-php-ext-enable igbinary

# Install memcached extension using PECL
RUN pecl install memcached-2.2.0 \
    && docker-php-ext-enable memcached

# Configure PHP extensions
RUN docker-php-ext-configure \
    gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j "$(nproc)" \
    opcache \
    exif \
    gd \
    gettext \
    gmp \
    hash \
    igbinary \
    intl \
    mbstring \
    mysql \
    mysqli \
    pcntl \
    pdo_mysql \
    readline \
    shmop \
    soap \
    sockets \
    tidy \
    wddx \
    xmlrpc \
    xsl \
    zip

# PHP configuration for Cloud Run
RUN set -ex; \
  { \
    echo "; Cloud Run enforces memory & timeouts"; \
    echo "memory_limit = -1"; \
    echo "max_execution_time = 0"; \
    echo "; File upload at Cloud Run network limit"; \
    echo "upload_max_filesize = 32M"; \
    echo "post_max_size = 32M"; \
    echo "; Configure Opcache for Containers"; \
    echo "opcache.enable = On"; \
    echo "opcache.validate_timestamps = Off"; \
    echo "; Configure Opcache Memory (Application-specific)"; \
    echo "opcache.memory_consumption = 32"; \
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"

# Copy in custom code from the host machine.
WORKDIR /var/www/html
COPY . .

# Set permissions and ownership
RUN chmod -R 775 . \
    && chown -R www-data:www-data /var/www/html/templates_c

# Use PORT environment variable in Apache configuration files
# Do this dynamically at runtime instead of build time
CMD sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf \
    && apache2-foreground
