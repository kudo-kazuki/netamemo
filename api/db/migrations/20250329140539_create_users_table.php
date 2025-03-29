<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users', ['comment' => '会員ユーザー情報']);

        $table
            ->addColumn('name', 'string', [
                'limit' => 100,
                'comment' => '表示名・ニックネーム'
            ])
            ->addColumn('email', 'string', [
                'limit' => 255,
                'comment' => 'ログイン用メールアドレス'
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'ログインパスワード（SNSログインの場合はnull）'
            ])
            ->addColumn('status', 'integer', [
                'default' => 0,
                'comment' => '会員ステータス: 0=仮登録, 1=本登録済, 2=アクセス禁止, 3=退会済'
            ])
            ->addColumn('last_login_at', 'datetime', [
                'null' => true,
                'comment' => '最終ログイン日時'
            ])
            ->addColumn('birthday', 'date', [
                'null' => true,
                'comment' => '生年月日'
            ])
            ->addColumn('gender', 'integer', [
                'null' => true,
                'comment' => '性別: 1=男性, 2=女性, 3=その他'
            ])
            ->addColumn('message', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => '一言メッセージ（プロフィール欄）'
            ])
            ->addColumn('profile', 'text', [
                'null' => true,
                'comment' => '自己紹介文（長文）'
            ])
            ->addColumn('notes', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => '管理者向け備考'
            ])
            ->addColumn('provider', 'string', [
                'limit' => 50,
                'null' => true,
                'comment' => 'SNSログインプロバイダ（google, facebook, twitter など）'
            ])
            ->addColumn('provider_user_id', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'SNSログイン時の一意なユーザーID'
            ])
            ->addColumn('created_at', 'datetime', [
                'comment' => '作成日時'
            ])
            ->addColumn('updated_at', 'datetime', [
                'comment' => '更新日時'
            ])
            ->addIndex(['email'], ['unique' => true, 'name' => 'idx_users_email_unique'])
            ->create();
    }
}
