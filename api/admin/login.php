<?php
require_once __DIR__ . '/../bootstrap.php';

use Firebase\JWT\JWT;
use Models\Admin;

header('Content-Type: application/json');

// リクエストボディを取得
$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$password = $input['password'] ?? '';

// 管理者をEloquentで取得
$admin = Admin::where('name', $name)->first();

if (!$admin || !password_verify($password, $admin->password)) {
    http_response_code(401);
    echo json_encode(['error' => '認証に失敗しました']);
    exit;
}

// JWTペイロード作成
$payload = [
    'sub' => $admin->id,
    'name' => $admin->name,
    'level' => $admin->level,
    'exp' => time() + 60 * 60 * 24, // 24時間有効
];

// JWT発行
$token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

// 成功レスポンス
echo json_encode(['token' => $token]);
