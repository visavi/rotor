<?php

use Phinx\Migration\AbstractMigration;

class CreateReadersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('readers')) {
            $table = $this->table('readers', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('relate_type', 'string', ['limit' => 50])
                ->addColumn('relate_id', 'integer')
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('created_at', 'integer')
                ->addIndex(['relate_type', 'relate_id', 'ip'], ['name' => 'relate_type'])
                ->create();
        }
    }
}
