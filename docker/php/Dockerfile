FROM php:7.2.27-fpm

LABEL maintainer "Paulius Stasiulionis <aurelijus@banelis.lt>"

WORKDIR /php

# Get composer: https://getcomposer.org/download/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN ln -s /php/composer.phar /usr/bin/composer

# Install depenedencies and PHP extensions
RUN apt-get update \
 && apt-get install -y libzip-dev bash-completion procps nano libicu-dev g++ libpng-dev libcurl4-gnutls-dev git wget gzip unzip \
 && docker-php-ext-install -j$(nproc) zip mysqli pdo_mysql intl calendar exif gd gettext pcntl sockets opcache \
 && rm -rf /var/lib/apt/lists/*

RUN pecl install redis \
    && docker-php-ext-enable redis

# xDebug helpers (do not use this in real production)
ADD enable_xdebug.sh /enable_xdebug.sh
ADD disable_xdebug.sh /disable_xdebug.sh
RUN pecl install xdebug-2.7.0 && \
    chmod +x /enable_xdebug.sh && \
    chmod +x /disable_xdebug.sh && \
    touch /usr/local/etc/php/conf.d/custom-xdebug.ini && \
    chmod 777 /usr/local/etc/php/conf.d/custom-xdebug.ini

# Add Symfony extension
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv ~/.symfony/bin/symfony /usr/local/bin/symfony



# Not root user
RUN useradd -c 'PHP user' -m -d /home/php -s /bin/bash php
USER php
ENV HOME /home/php

# Configure git, so there would not be errors running "symfony new my_project"
RUN git config --global user.email "docker.fake.user@example.com" && git config --global user.name "Docker fake user"

# xDebug configuration
ENV PHP_IDE_CONFIG serverName=nfqKickStartDocker

WORKDIR /code
VOLUME /code