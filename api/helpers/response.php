<?php
// レスポンスをJSONで返す（成功・失敗どちらも）
function json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// エラーレスポンス用（統一された形式で返す）
function error(string $message, int $status = 400): void
{
    json(['message' => $message], $status);
}
