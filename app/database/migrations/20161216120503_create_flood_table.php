<?php

use Phinx\Migration\AbstractMigration;

class CreateFloodTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('flood')) {
            $table = $this->table('flood', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('page', 'string', ['limit' => 30])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->create();
        }
    }
}
