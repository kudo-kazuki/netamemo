<?php
require_once __DIR__ . '/vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'local';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
$dotenv->safeLoad(); // 存在しないファイルでもエラー出したくない場合は safeLoad()

use Illuminate\Database\Capsule\Manager as Capsule;

// Eloquent ORM 設定
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'timezone'  => $_ENV['DB_TIMEZONE'] ?? '+00:00',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 共通関数を読み込み
require_once __DIR__ . '/helpers/request.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/validation.php';
