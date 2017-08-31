<?php

use Phinx\Migration\AbstractMigration;

class CreateChangemailTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('changemail')) {
            $table = $this->table('changemail', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('mail', 'string', ['limit' => 50])
                ->addColumn('hash', 'string', ['limit' => 25])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
