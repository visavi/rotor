<?php

use Phinx\Migration\AbstractMigration;

class CreateWallTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('wall')) {
            $table = $this->table('wall', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->create();
        }
    }
}
