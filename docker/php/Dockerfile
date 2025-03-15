FROM php:8.2.4-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    wget \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libmcrypt-dev \
	libpng-dev \
    git \
    zsh \
    ssh \
    nano \
    libzip-dev \
    zip \
    librabbitmq-dev \
    gnupg \
    libicu-dev \
    libpq-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	&& docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install bcmath \
	&& docker-php-ext-install zip \
	&& docker-php-ext-install pdo

RUN docker-php-ext-install sysvsem
RUN pecl install amqp && docker-php-ext-enable amqp

RUN pecl install pcov && docker-php-ext-enable pcov
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-enable intl

RUN useradd -ms /bin/bash bluegrid
RUN adduser bluegrid www-data

#
WORKDIR /app
