<?php
require_once __DIR__ . '/../bootstrap.php';

use Firebase\JWT\JWT;
use Models\User;
use MessagePack\Packer;
use MessagePack\BufferUnpacker;

// MessagePackでリクエスト受け取り
$raw = file_get_contents('php://input');
$unpacker = new BufferUnpacker();
$unpacker->reset($raw);
$input = $unpacker->unpack();

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

// ユーザーをEloquentで取得
$user = User::where('email', $email)->first();

// 存在しない or パスワード不一致
if (!$user || !password_verify($password, $user->password)) {
    http_response_code(401);
    header('Content-Type: application/x-msgpack');
    $packer = new Packer();
    echo $packer->pack(['message' => '認証に失敗しました']);
    exit;
}

// 本登録済み（status = 1）でない場合はログイン拒否
if ((int)$user->status !== 1) {
    http_response_code(403);
    header('Content-Type: application/x-msgpack');
    $packer = new Packer();
    echo $packer->pack(['message' => '本登録が完了していないか、利用できない状態です']);
    exit;
}

// JWTペイロード作成
$payload = [
    'sub' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'role' => 'user',
    'exp' => time() + 60 * 60 * 24, // 24時間有効
];

// JWT発行
$token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

// 最終ログイン日時を更新
$user->last_login_at = date('Y-m-d H:i:s');
$user->save();

// 成功レスポンス
$packer = new Packer();
header('Content-Type: application/x-msgpack');
echo $packer->pack(['token' => $token]);
