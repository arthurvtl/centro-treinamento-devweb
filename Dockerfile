# Imagem de produção do Sistema CT (deploy no Render).
# PHP 8.3 + Apache, com as extensões exigidas pelo CodeIgniter 4 (intl, mbstring, mysqli).
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev unzip \
    && docker-php-ext-install intl mysqli zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependências primeiro, para aproveitar o cache de camadas do Docker.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && chown -R www-data:www-data writable

# O Apache passa a servir a pasta public/ (padrão do CodeIgniter) e aceita o .htaccess.
COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf
COPY docker/iniciar.sh /usr/local/bin/iniciar.sh
RUN chmod +x /usr/local/bin/iniciar.sh

CMD ["iniciar.sh"]
