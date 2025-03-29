<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';           // テーブル名
    protected $primaryKey = 'id';         // 主キー
    public $timestamps = true;            // created_at, updated_at を自動管理

    // 書き込み可能なカラム
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'last_login_at',
        'birthday',
        'gender',
        'message',
        'profile',
        'notes',
        'provider',
        'provider_user_id',
    ];

    // レスポンスから隠したいカラム
    protected $hidden = [
        'password',
        'provider_user_id',
    ];
}
