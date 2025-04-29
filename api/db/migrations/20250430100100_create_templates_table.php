<?php
use Phinx\Migration\AbstractMigration;

class CreateTemplatesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('templates');
        $table->addColumn('user_id', 'integer', ['signed' => false])
              ->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('visibility', 'integer', ['default' => 0])
              ->addTimestamps()
              ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
              ->create();
    }
}
