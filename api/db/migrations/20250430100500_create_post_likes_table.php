<?php
use Phinx\Migration\AbstractMigration;

class CreatePostLikesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('post_likes');
        $table->addColumn('user_id', 'integer', ['signed' => false])
              ->addColumn('post_id', 'integer', ['signed' => false])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
              ->addForeignKey('post_id', 'posts', 'id', ['delete'=> 'CASCADE'])
              ->addIndex(['user_id', 'post_id'], ['unique' => true])
              ->create();
    }
}
