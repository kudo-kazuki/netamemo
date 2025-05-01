<?php
require_once __DIR__ . '/../bootstrap.php';

use Models\Template;
use Models\TemplateHeading;

class TemplateController
{
    public function create(array $input): void
    {
        $authUser = requireUser();
        $userId = $authUser->sub;

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

    public function list(): void
    {
        $authUser = requireUser(); // 認証済みユーザーを取得
        $userId = $authUser->sub;
    
        try {
            error_log("Template list start for user_id: {$userId}");

            // templates + headings を一括取得
            $templates = Template::with('headings')
                ->where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->get();

            // 整形（フロントに優しい形にする）
            $result = $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'title' => $template->title,
                    'visibility' => $template->visibility,
                    'created_at' => $template->created_at->format('Y-m-d H:i:s'), /*CarbonオブジェクトのままだとMessagePackでシリアライズできないため、文字列に変換*/
                    'updated_at' => $template->updated_at->format('Y-m-d H:i:s'),
                    'headings' => $template->headings->map(function ($heading) {
                        return [
                            'id' => $heading->id,
                            'heading_order' => $heading->heading_order,
                            'heading_title' => $heading->heading_title,
                        ];
                    })->sortBy('heading_order')->values()->all(),
                ];
            })->toArray();

            msgpack_response($result);
        } catch (Exception $e) {
            msgpack_response([
                'message' => '一覧の取得に失敗しました',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function find(array $input): void
    {
        $user = requireUser();

        $template = Template::with('headings')
            ->where('id', $input['id'] ?? 0)
            ->where('user_id', $user->sub)
            ->first();

        if (!$template) {
            msgpack_response(['message' => 'テンプレートが存在しないか、閲覧権限がありません'], 404);
        }

        $result = [
            'id' => $template->id,
            'title' => $template->title,
            'visibility' => $template->visibility,
            'created_at' => $template->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $template->updated_at->format('Y-m-d H:i:s'),
            'headings' => $template->headings->map(function ($heading) {
                return [
                    'id' => $heading->id,
                    'heading_order' => $heading->heading_order,
                    'heading_title' => $heading->heading_title,
                ];
            })->sortBy('heading_order')->values()->all(),
        ];

        msgpack_response($result);
    }

    public function update(array $input): void
    {
        $user = requireUser();
    
        $template = Template::with('headings')
            ->where('id', $input['id'] ?? 0)
            ->where('user_id', $user->sub)
            ->first();
    
        if (!$template) {
            msgpack_response(['message' => 'テンプレートが見つかりません'], 404);
        }
    
        // バリデーション
        $rules = [
            'title' => 'required|max:255',
            'visibility' => 'required|numeric',
        ];
    
        $errors = validate($input, $rules);
    
        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }
    
        // タイトル重複チェック（別のテンプレートと重複していないか）
        $duplicate = Template::where('user_id', $user->sub)
            ->where('title', $input['title'])
            ->where('id', '!=', $template->id)
            ->exists();
    
        if ($duplicate) {
            msgpack_response(['message' => '同じタイトルのテンプレートが既に存在します'], 409);
        }
    
        try {
            // 本体更新
            $template->title = $input['title'];
            $template->visibility = $input['visibility'];
            $template->save();
    
            // 見出しの更新（上書き）
            $template->headings()->delete(); // 一旦全削除して
            foreach ($input['headings'] as $headingData) {
                $template->headings()->create([
                    'heading_order' => $headingData['heading_order'],
                    'heading_title' => $headingData['heading_title'],
                ]);
            }
    
            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            msgpack_response([
                'message' => 'テンプレートの更新に失敗しました',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function delete(array $input): void
    {
        $user = requireUser();
    
        $template = Template::where('id', $input['id'])
            ->where('user_id', $user->sub)
            ->first();
    
        if (!$template) {
            msgpack_response(['message' => 'テンプレートが見つからないか、削除権限がありません'], 404);
        }

        try {
            // 関連データの削除（CASCADEならこの1行だけでOK）
            $template->delete();
    
            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            error_log('[TEMPLATE_DELETE_ERROR] ' . $e->getMessage());
            msgpack_response([
                'message' => 'テンプレート削除に失敗しました',
                'error' => $e->getMessage(),
            ], 500);
        }
    }    
}
