<?php
require_once __DIR__ . '/../bootstrap.php';

use Models\Template;
use Models\TemplateHeading;

class TemplateController
{
    public function create(int $userId, array $input): void
    {
        $title = $input['title'] ?? '';
        $visibility = $input['visibility'] ?? 0;
        $headings = $input['headings'] ?? [];

        // バリデーション
        $errors = [];

        if ($title === '') {
            $errors['title'][] = 'タイトルは必須です';
        }

        if (!is_int($visibility) || !in_array($visibility, [0, 1, 2], true)) {
            $errors['visibility'][] = '公開範囲が不正です';
        }

        if (!is_array($headings) || count($headings) === 0) {
            $errors['headings'][] = '見出しが1つ以上必要です';
        } else {
            foreach ($headings as $index => $heading) {
                if (!isset($heading['heading_order'], $heading['heading_title'])) {
                    $errors["headings[$index]"][] = '見出しデータが不正です';
                }
            }
        }

        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }

        // 重複チェック
        $existing = Template::where('user_id', $userId)
                            ->where('title', $title)
                            ->first();

        if ($existing) {
            msgpack_response([
                'message' => '同じタイトルのテンプレートは既に存在します'
            ], 409);
        }

        // 保存処理（トランザクション）
        try {
            $capsule = new \Illuminate\Database\Capsule\Manager();
            $capsule::connection()->beginTransaction();

            $template = new Template();
            $template->user_id = $userId;
            $template->title = $title;
            $template->visibility = $visibility;
            $template->save();

            foreach ($headings as $heading) {
                $template->headings()->create([
                    'heading_order' => $heading['heading_order'],
                    'heading_title' => $heading['heading_title'],
                ]);
            }

            $capsule::connection()->commit();

            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            $capsule::connection()->rollBack();

            msgpack_response([
                'message' => 'テンプレート作成に失敗しました',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
