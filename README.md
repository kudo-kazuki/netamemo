## 🚀 初回セットアップ手順

### 1. Dockerコンテナのビルド＆起動

docker-compose up -d --build
※初回は時間がかかる場合があります。

### 2. PHPコンテナに入る

docker exec -it php bash

### 3. composer install を実行（初回のみ）

cd /var/www/api
composer install --no-dev --optimize-autoloader
完了すると /api/vendor/ フォルダが生成されます。
※次回以降この手順は不要です。

### 4. MySQL & phpMyAdmin の確認

phpMyAdmin: http://localhost:8081
ログイン情報：
ユーザー名: root
パスワード: root

### 5. API動作確認（例）

APIルートに簡単なファイルを配置して確認できます。
// /api/test.php

<?php
echo 'test';
ブラウザでアクセス：http://localhost:8082/api/test.php

### 6. フロントエンド開発の起動（Vite）
npm ci
npm run dev
アクセス：http://localhost:5173
