# Etapa 1: dependências PHP + Composer (base Alpine)
FROM composer:lts as deps

WORKDIR /app

# Instala bibliotecas necessárias e extensões PHP
RUN apk update && apk add --no-cache \
    libzip-dev \
    unzip \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip 

# Copia os arquivos da aplicação
COPY . .

# Instala as dependências PHP
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

# --------------------------------------------------------

# Etapa 2: imagem final com Apache + PHP + extensões
FROM php:8.2-apache as final

# Instala as bibliotecas do sistema e extensões faltantes
RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    telnet \
    unzip \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd zip mbstring xml bcmath mysqli

RUN a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && a2enmod env


# Copia configuração do PHP (se existir)
COPY ./php.ini "$PHP_INI_DIR/php.ini"

# Copia dependências instaladas na etapa anterior
COPY --from=deps /app/vendor/ /var/www/html/vendor

# Copia o restante da aplicação
COPY . /var/www/html

# Define permissões de execução
USER www-data
