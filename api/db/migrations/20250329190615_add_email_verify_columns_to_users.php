<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmailVerifyColumnsToUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('users');

        $table
            ->addColumn('email_verify_token', 'string', [
                'null' => true,
                'default' => null,
                'comment' => 'メール認証用トークン（UUID）',
            ])
            ->addColumn('email_verify_token_created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'comment' => 'メール認証トークン発行日時',
            ])
            ->addColumn('email_verified_at', 'datetime', [
                'null' => true,
                'default' => null,
                'comment' => 'メール認証完了日時',
            ])
            ->update();
    }
}
