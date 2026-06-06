FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN chmod +x /var/www/html/start.sh

ENV PORT=10000

EXPOSE 10000

CMD ["/var/www/html/start.sh"]
