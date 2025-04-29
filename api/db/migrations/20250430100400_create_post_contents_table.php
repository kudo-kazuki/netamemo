<?php
use Phinx\Migration\AbstractMigration;

class CreatePostContentsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('post_contents');
        $table->addColumn('post_id', 'integer', ['signed' => false])
              ->addColumn('heading_id', 'integer', ['signed' => false])
              ->addColumn('content', 'text')
              ->addTimestamps()
              ->addForeignKey('post_id', 'posts', 'id', ['delete'=> 'CASCADE'])
              ->addForeignKey('heading_id', 'template_headings', 'id', ['delete'=> 'SET NULL'])
              ->create();
    }
}
