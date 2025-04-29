<?php
use Phinx\Migration\AbstractMigration;

class CreatePostsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('posts');
        $table->addColumn('user_id', 'integer', ['signed' => false])
              ->addColumn('template_id', 'integer', ['signed' => false])
              ->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('like_count', 'integer', ['default' => 0])
              ->addTimestamps()
              ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
              ->addForeignKey('template_id', 'templates', 'id', ['delete'=> 'SET NULL'])
              ->create();
    }
}
