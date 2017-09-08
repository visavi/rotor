<?php

use Phinx\Migration\AbstractMigration;

class CreatePollingsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('pollings')) {
            $table = $this->table('pollings', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('relate_type', 'string', ['limit' => 20])
                ->addColumn('relate_id', 'integer')
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('vote', 'boolean', ['default' => true])
                ->addColumn('time', 'integer')
                ->addIndex(['relate_type', 'relate_id', 'user'], ['name' => 'relate_type_idx'])
                ->create();
        }
    }
}
