<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TemplateController.php';

$input = getMsgPackInput();
$action = getAction($input);

$controller = new TemplateController();

switch ($action) {
    case 'create':
        $controller->create($input);
        break;
    case 'list':
        $controller->list(); // ← tokenはヘッダーから自動取得
        break;
    case 'delete':
        $controller->delete($input);
        break;
    case 'find':
        $controller->find($input);
        break;
    case 'update':
        $controller->update($input);
        break;

    default:
        msgpack_response(['message' => 'アクションが無効です'], 400);
        break;
}
