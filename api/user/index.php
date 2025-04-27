<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/UserController.php';

$input = getMsgPackInput();
$action = getAction($input);

$controller = new UserController();

switch ($action) {
    case 'create':
        $controller->create($input);
        break;
    case 'getInfo':
        $controller->getInfo();
        break;
    case 'update':
        $controller->update($input);
        break;
    default:
        msgpack_response('アクションが無効です', 400);
        break;
}
