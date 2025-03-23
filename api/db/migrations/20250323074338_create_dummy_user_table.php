<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDummyUserTable extends AbstractMigration
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
        $table = $this->table('dummy_user');
        $table
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('gender', 'boolean', ['comment' => '0: 男性, 1: 女性']) // tinyint(1) 扱い
            ->addColumn('age', 'integer', ['signed' => false])
            ->addColumn('remarks', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->create();
    }
}
