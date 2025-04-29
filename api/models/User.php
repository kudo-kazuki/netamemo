<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // バリデーションルール（新規作成）
    public static function createRules(): array
    {
        return [
            'name'     => 'required|max:100',
            'email'    => 'required|max:255',
            'password' => 'required|max:255',
            'birthday' => 'nullable|date',
            'gender'   => 'nullable|numeric',
            'message'  => 'nullable|max:255',
            'profile'  => 'nullable|max:2000',
            'notes'    => 'nullable|max:255',
        ];
    }

    // バリデーションルール（更新時など）
    public static function updateRules(): array
    {
        return [
            'name'     => 'required|max:100',
            'birthday' => 'nullable|date',
            'gender'   => 'nullable|numeric',
            'message'  => 'nullable|max:255',
            'profile'  => 'nullable|max:2000',
            'notes'    => 'nullable|max:255',
        ];
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function postLikes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }
}
