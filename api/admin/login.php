<?php
require_once __DIR__ . '/../bootstrap.php';

use Firebase\JWT\JWT;
use Models\Admin;
use MessagePack\Packer;
use MessagePack\BufferUnpacker;

// MessagePackでリクエスト受け取り
$raw = file_get_contents('php://input');
$unpacker = new BufferUnpacker();
$unpacker->reset($raw);
$input = $unpacker->unpack();

$name = $input['name'] ?? '';
$password = $input['password'] ?? '';

// 管理者をEloquentで取得
$admin = Admin::where('name', $name)->first();

if (!$admin || !password_verify($password, $admin->password)) {
    http_response_code(401);
    header('Content-Type: application/x-msgpack');
    $packer = new Packer();
    echo $packer->pack(['message' => '認証に失敗しました']);
    exit;
}

// JWTペイロード作成
$payload = [
    'sub' => $admin->id,
    'name' => $admin->name,
    'level' => $admin->level,
    'role' => 'admin',
    'exp' => time() + 60 * 60 * 24, // 24時間有効
];

// JWT発行
$token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

// 成功レスポンス
$packer = new Packer();
header('Content-Type: application/x-msgpack');
echo $packer->pack(['token' => $token]);
