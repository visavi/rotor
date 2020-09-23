<?php

use Phinx\Migration\AbstractMigration;

class CreateFilesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('files')) {
            $table = $this->table('files', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('relate_type', 'string', ['limit' => 10])
                ->addColumn('relate_id', 'integer')
                ->addColumn('hash', 'string', ['limit' => 100])
                ->addColumn('name', 'string', ['limit' => 60])
                ->addColumn('size', 'integer')
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addIndex(['relate_type', 'relate_id'], ['name' => 'relate_type'])
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
