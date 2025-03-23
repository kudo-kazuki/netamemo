#!/bin/bash

cd /var/www/api

# 初回のみ vendor がない場合は composer install
if [ ! -d "vendor" ]; then
  composer install --no-dev --optimize-autoloader
fi

# マイグレーション自動実行
./vendor/bin/phinx migrate

# php-fpm をフォアグラウンドで実行
php-fpm
