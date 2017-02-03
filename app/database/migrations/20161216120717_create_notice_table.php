<?php

use Phinx\Migration\AbstractMigration;

class CreateNoticeTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('notice')) {
            $table = $this->table('notice', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('time', 'integer')
                ->addColumn('protect', 'boolean', ['default' => 0])
                ->create();
        }
    }
}
