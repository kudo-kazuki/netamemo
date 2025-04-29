<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TemplateController.php';

$input = getMsgPackInput();
$action = getAction($input);
$user = requireUser(); // JWT認証（ログイン済みユーザー）

$controller = new TemplateController();

switch ($action) {
    case 'create':
        $controller->create($user->sub, $input); // user_idを明示的に渡す
        break;
    default:
        msgpack_response(['message' => 'アクションが無効です'], 400);
        break;
}
