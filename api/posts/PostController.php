<?php
require_once __DIR__ . '/../bootstrap.php';

use Models\Post;
use Models\PostContent;
use Illuminate\Database\Capsule\Manager as DB;

class PostController
{
    public function create(array $input): void
    {
        $authUser = requireUser(); // 認証チェック
        $userId = $authUser->sub ?? null;

        // バリデーション
        $rules = [
            'title' => 'required|max:255',
            'template_id' => 'required|numeric',
            'contents' => 'required',
        ];
        $errors = validate($input, $rules);

        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }

        try {
            DB::connection()->beginTransaction(); // トランザクション開始

            // 投稿本体
            $post = new Post();
            $post->user_id = $userId;
            $post->template_id = $input['template_id'];
            $post->title = $input['title'];
            $post->save();

            // 投稿内容
            foreach ($input['contents'] as $contentItem) {
                $postContent = new PostContent();
                $postContent->post_id = $post->id;
                $postContent->heading_id = $contentItem['heading_id'];
                $postContent->content = $contentItem['content'];
                $postContent->save();
            }

            DB::connection()->commit(); // コミット

            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            DB::connection()->rollBack(); // ロールバック
            msgpack_response(['message' => '投稿の保存に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }

    public function listByTemplate(array $input): void
    {
        $authUser = requireUser();
        $userId = $authUser->sub;

        $templateId = $input['template_id'] ?? null;
        $page = max((int)($input['page'] ?? 1), 1);
        $perPage = (int)($input['per_page'] ?? 50);
        $sortOrder = strtolower($input['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if (!$templateId) {
            msgpack_response(['message' => 'テンプレートIDは必須です'], 400);
        }

        // 所有権チェック
        $template = Template::where('id', $templateId)->where('user_id', $userId)->first();
        if (!$template) {
            msgpack_response(['message' => 'テンプレートが存在しないか、アクセス権がありません'], 404);
        }

        // 投稿一覧取得
        $query = Post::with('contents')
            ->where('template_id', $templateId)
            ->orderBy('created_at', $sortOrder);

        $total = $query->count();
        $posts = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'template_id' => $post->template_id,
                    'title' => $post->title,
                    'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
                    'contents' => $post->contents
                        ->sortBy('heading_order')
                        ->map(function ($c) {
                            return [
                                'heading_order' => $c->heading_order,
                                'content' => $c->content,
                            ];
                        })->values(),
                ];
            });

        msgpack_response([
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'posts' => $posts,
        ]);
    }

}
