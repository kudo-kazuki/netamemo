<?php
require_once __DIR__ . '/../bootstrap.php';
use Models\Admin;

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
}
