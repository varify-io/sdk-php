ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

# What shopware/core needs, for analysing and testing the Shopware plugin.
RUN install-php-extensions gd intl pdo_mysql zip

WORKDIR /app
