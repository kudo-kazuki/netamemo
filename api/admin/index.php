<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/AdminController.php';

$input = getJsonInput();
$action = getAction($input);
$user = requireAuth(); // 認証されていなければ 401 を返して終了

$controller = new AdminController();

switch ($action) {
    case 'list':
        $controller->list();
        break;
    case 'create':
        $controller->create($input);
        break;
    case 'edit':
        $controller->edit($input);
        break;
    case 'delete':
        $controller->delete($input);
        break;
    default:
        error('アクションが無効です', 400);
        break;
}
