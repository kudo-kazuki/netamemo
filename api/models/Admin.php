<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';      // テーブル名
    protected $primaryKey = 'id';     // 主キー
    public $timestamps = true;        // created_at, updated_at 自動管理

    // パスワードなど隠したいカラム
    protected $hidden = ['password'];
}
