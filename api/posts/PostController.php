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
}
