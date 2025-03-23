# PHP 8.4 FPM イメージを使用
FROM php:8.4-fpm

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Composer を使えるようにする
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/api

# api ディレクトリ全体をコピー
COPY ./api /var/www/api

# entrypoint.sh を追加
COPY ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ENTRYPOINT を差し替える
ENTRYPOINT ["bash", "/usr/local/bin/entrypoint.sh"]
