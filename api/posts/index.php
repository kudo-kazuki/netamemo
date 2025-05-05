<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/PostController.php';

$input = getMsgPackInput();
$action = getAction($input);

$controller = new PostController();

switch ($action) {
    case 'create':
        $controller->create($input);
        break;
    case 'listByTemplate':
        $controller->listByTemplate($input);
        break;




    default:
        msgpack_response(['message' => 'アクションが無効です'], 400);
        break;
}
