# -------------------------
# Dockerfile para Slim + Swoole
# -------------------------
FROM php:8.3-cli

# Instala dependências do sistema e extensões
RUN apt-get update && apt-get install -y \
    git zip unzip libssl-dev pkg-config libcurl4-openssl-dev \
    && pecl install swoole mongodb-1.21.0 \
    && docker-php-ext-enable swoole mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto
COPY . .

# Instala dependências PHP
RUN composer install --no-interaction --optimize-autoloader

# Expõe a porta usada pelo Swoole
EXPOSE 9501

# Comando padrão: roda o servidor Swoole
CMD ["php", "src/server.php"]
