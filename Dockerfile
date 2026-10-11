# syntax=docker/dockerfile:1
FROM php:8.2-apache-bookworm AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig5 \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && printf '%s\n' '<Directory /var/www/html>' 'AllowOverride All' 'Options -Indexes' '</Directory>' \
        > /etc/apache2/conf-available/nmc.conf \
    && a2enconf nmc \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

FROM base AS extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring

FROM base AS production
COPY --from=extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
RUN php -r 'exit(extension_loaded("pdo_mysql") && extension_loaded("mbstring") && extension_loaded("fileinfo") ? 0 : 1);'
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
RUN mkdir -p uploads/media && chown -R www-data:www-data uploads
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/") === false ? 1 : 0);'
CMD ["apache2-foreground"]
