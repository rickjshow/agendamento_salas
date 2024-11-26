# Dockerfile para Laravel com PHP 8.2
FROM php:8.2-fpm

# Instala dependências e extensões
RUN apt-get update && apt-get install -y \
    zip unzip git curl libpng-dev libonig-dev libxml2-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configura o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto
COPY . /var/www

# Instala as dependências do Laravel
RUN composer install --optimize-autoloader --no-dev

# Define permissões
RUN chown -R www-data:www-data /var/www

CMD php artisan serve --host=0.0.0.0 --port=8000
