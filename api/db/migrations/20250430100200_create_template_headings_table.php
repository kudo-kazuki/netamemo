<?php
use Phinx\Migration\AbstractMigration;

class CreateTemplateHeadingsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('template_headings');
        $table->addColumn('template_id', 'integer', ['signed' => false])
              ->addColumn('heading_order', 'integer')
              ->addColumn('heading_title', 'string', ['limit' => 255])
              ->addTimestamps()
              ->addForeignKey('template_id', 'templates', 'id', ['delete'=> 'CASCADE'])
              ->create();
    }
}
