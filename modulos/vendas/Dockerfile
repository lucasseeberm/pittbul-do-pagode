# Usa uma imagem oficial do PHP com o servidor Apache
FROM php:8.2-apache

# Copia todo o código da pasta atual para o diretório do servidor web
COPY . /var/www/html/

# Instala dependências e extensões PHP
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql curl zip

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Apenas garante que a pasta de uploads exista. As permissões serão tratadas pelo K8s.
RUN mkdir -p /var/www/html/uploads

# Expõe a porta 80
EXPOSE 80