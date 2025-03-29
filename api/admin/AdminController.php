<?php
require_once __DIR__ . '/../bootstrap.php';

use Models\Admin;
use Models\User;

class AdminController
{
    public function list()
    {
        return Admin::all()->toArray();
    }

    public function create(array $input)
    {
        $rules = [
            'name' => 'required|max:50',
            'password' => 'required|max:255',
            'level' => 'required|numeric',
            'remarks' => 'max:255',
        ];
    
        $errors = validate($input, $rules);
    
        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }
    
        try {
            $admin = new Admin();
            $admin->name = $input['name'];
            $admin->level = $input['level'];
            $admin->remarks = $input['remarks'] ?? '';
            $admin->password = password_hash($input['password'], PASSWORD_DEFAULT);
            $admin->save();
    
            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            msgpack_response(['message' => '管理者作成に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit(array $input)
    {
        // バリデーションルール定義
        $rules = [
            'id' => 'required|numeric',
            'name' => 'required|max:50',
            'level' => 'required|numeric',
            'remarks' => 'max:255',
            // パスワードは任意
            'password' => 'max:255',
        ];
    
        $errors = validate($input, $rules);
    
        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }
    
        try {
            $admin = Admin::findOrFail($input['id']);
            $admin->name = $input['name'];
            $admin->level = $input['level'];
            $admin->remarks = $input['remarks'] ?? '';

            // パスワードが送られてきていて空でなければ更新
            if (!empty($input['password'])) {
                $admin->password = password_hash($input['password'], PASSWORD_DEFAULT);
            }

            $admin->save();
    
            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            msgpack_response(['message' => '更新処理に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }

    public function delete(array $input)
    {
        $rules = [
            'id' => 'required|numeric',
        ];
    
        $errors = validate($input, $rules);
    
        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }
    
        try {
            $deleted = Admin::destroy($input['id']);
    
            if ($deleted) {
                msgpack_response(['success' => true]);
            } else {
                msgpack_response(['message' => '指定された管理者が見つかりません'], 404);
            }
        } catch (Exception $e) {
            msgpack_response(['message' => '削除処理に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }

    public function userList(array $input)
    {
        // 管理者以外は拒否
        $authUser = requireAuth();
        if ($authUser->level !== 0) {
            error('許可されていない操作です', 403);
        }

        // クエリビルダ開始
        $query = User::query();

        // ===== フィルタ処理 =====
        if (!empty($input['name'])) {
            $query->where('name', 'like', '%' . $input['name'] . '%');
        }
        if (!empty($input['email'])) {
            $query->where('email', 'like', '%' . $input['email'] . '%');
        }
        if (!empty($input['message'])) {
            $query->where('message', 'like', '%' . $input['message'] . '%');
        }
        if (!empty($input['profile'])) {
            $query->where('profile', 'like', '%' . $input['profile'] . '%');
        }
        if (!empty($input['notes'])) {
            $query->where('notes', 'like', '%' . $input['notes'] . '%');
        }

        if (isset($input['status']) && $input['status'] !== '' && is_numeric($input['status'])) {
            $query->where('status', (int) $input['status']);
        }
        
        if (isset($input['status']) && $input['status'] !== '' && is_numeric($input['status'])) {
            $status = (int) $input['status'];
            $query->where('status', $status);
        } else {
            // 明示的に status 指定がないときは「退会済み（3）」を除外
            $query->where('status', '<>', 3);
        }

        if (!empty($input['birthday_start'])) {
            $query->where('birthday', '>=', $input['birthday_start']);
        }

        if (!empty($input['birthday_end'])) {
            $query->where('birthday', '<=', $input['birthday_end']);
        }

        if (!empty($input['last_login_start'])) {
            $query->where('last_login_at', '>=', $input['last_login_start']);
        }

        if (!empty($input['last_login_end'])) {
            $query->where('last_login_at', '<=', $input['last_login_end']);
        }

        // ===== ソート処理 =====
        $sortMap = [
            'id_asc' => ['id', 'asc'],
            'id_desc' => ['id', 'desc'],
            'created_at_asc' => ['created_at', 'asc'],
            'created_at_desc' => ['created_at', 'desc'],
            'updated_at_asc' => ['updated_at', 'asc'],
            'updated_at_desc' => ['updated_at', 'desc'],
        ];

        $sortKey = $input['sort'] ?? 'id_desc';
        [$sortCol, $sortDir] = $sortMap[$sortKey] ?? ['id', 'desc'];

        $query->orderBy($sortCol, $sortDir);

        // データ取得
        $users = $query->get();

        return $query->get()->toArray();
    }

    public function userStatusChange(array $input)
    {
        $rules = [
            'id' => 'required|numeric',
            'status' => 'required|numeric',
        ];
    
        $errors = validate($input, $rules);
    
        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }
    
        try {
            $user = User::find($input['id']);
    
            if (!$user) {
                msgpack_response(['message' => 'ユーザーが見つかりません'], 404);
            }
    
            $user->status = $input['status'];
            $user->save();
    
            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            msgpack_response(['message' => 'ステータス変更に失敗しました', 'error' => $e->getMessage()], 500);
        }
    }
}
