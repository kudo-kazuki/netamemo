<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// JSON入力を取得
function getJsonInput(): array
{
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// actionパラメータ取得
function getAction(array $input): ?string
{
    return $input['action'] ?? null;
}

// JWT認証トークンを検証し、ユーザー情報を返す
function getAuthenticatedUser(): object
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    return JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
}

// 認証済みでなければ401を返す
function requireAuth(): object
{
    try {
        return getAuthenticatedUser();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
        exit;
    }
}
