<?php
require_once __DIR__ . '/../bootstrap.php';
use Models\Admin;

class AdminController
{
    public function list()
    {
        $admins = Admin::all();
        json($admins);
    }

    public function create(array $input)
    {
        $admin = new Admin();
        $admin->name = $input['name'];
        $admin->level = $input['level'];
        $admin->remarks = $input['remarks'];
        $admin->password = password_hash($input['password'], PASSWORD_DEFAULT);
        $admin->save();

        json(['success' => true]);
    }

    public function edit(array $input)
    {
        // バリデーションルール定義
        $rules = [
            'id' => 'required|numeric',
            'name' => 'required|max:50',
            'level' => 'required|numeric',
            'remarks' => 'max:255',
        ];

        $errors = validate($input, $rules);

        if (!empty($errors)) {
            error(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }

        $admin = Admin::findOrFail($input['id']);
        $admin->name = $input['name'];
        $admin->level = $input['level'];
        $admin->remarks = $input['remarks'];
        $admin->save();

        json(['success' => true]);
    }

    public function delete(array $input)
    {
        Admin::destroy($input['id']);
        json(['success' => true]);
    }
}
