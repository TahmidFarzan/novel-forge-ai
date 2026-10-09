FROM php:8.4-fpm-alpine

ENV COMPOSER_HOME=/tmp/composer \
    npm_config_cache=/tmp/.npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-alpine /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-alpine /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

RUN apk add --no-cache \
        bash \
        curl \
        git \
        unzip \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        sqlite-dev \
        su-exec \
        libstdc++ \
        libgcc \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_sqlite \
        zip \
    && rm -rf /var/cache/apk/* /tmp/pear /tmp/composer

RUN mkdir -p \
        /var/www/html/novel-forge-ai/storage/framework/cache/data \
        /var/www/html/novel-forge-ai/storage/framework/sessions \
        /var/www/html/novel-forge-ai/storage/framework/views \
        /var/www/html/novel-forge-ai/storage/logs \
        /var/www/html/novel-forge-ai/storage/app/public \
        /var/www/html/novel-forge-ai/bootstrap/cache \
        /var/www/html/novel-forge-ai/database \
        /var/www/html/novel-forge-ai/vendor \
        /var/www/html/novel-forge-ai/node_modules \
    && chown -R www-data:www-data /var/www/html

COPY php.ini /usr/local/etc/php/conf.d/99-novel-forge-ai.ini
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
COPY startup.sh /usr/local/bin/startup.sh

RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh /usr/local/bin/startup.sh \
    && chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/startup.sh

WORKDIR /var/www/html/novel-forge-ai

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
