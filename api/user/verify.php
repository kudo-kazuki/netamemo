<?php
require_once __DIR__ . '/../bootstrap.php';

use Models\User;

// クエリパラメータからトークン取得
$token = $_GET['token'] ?? '';

if (!$token) {
    http_response_code(400);
    exit('無効なリクエストです。');
}

// トークン一致するユーザーを探す
$user = User::where('email_verify_token', $token)->first();

if (!$user) {
    http_response_code(404);
    exit('このURLは無効です。');
}

// トークンの発行から24時間以内かチェック
$createdAt = strtotime($user->email_verify_token_created_at ?? '');
$expiresAt = $createdAt + (60 * 60 * 24); // 24時間
if (time() > $expiresAt) {
    http_response_code(410);
    exit('このURLの有効期限が切れています。');
}

// ステータス更新と検証日時記録
$user->status = 1; // 本登録済
$user->email_verified_at = date('Y-m-d H:i:s');
$user->email_verify_token = null;
$user->email_verify_token_created_at = null;
$user->save();

// 表示（JSONじゃなくてブラウザ用）
echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>本登録完了</title>
</head>
<body>
  <h1>本登録が完了しました！</h1>
  <p>ログインページからログインしてください。</p>
</body>
</html>
HTML;
