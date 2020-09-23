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
            $table = $this->table('readers', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('relate_type', 'string', ['limit' => 10])
                ->addColumn('relate_id', 'integer')
                ->addColumn('ip', 'varbinary', ['limit' => 16])
                ->addColumn('created_at', 'integer')
                ->addIndex(['relate_type', 'relate_id', 'ip'], ['name' => 'relate_type'])
                ->create();
        }
    }
}
