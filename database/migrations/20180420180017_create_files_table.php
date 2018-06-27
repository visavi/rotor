<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateFilesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('files')) {
            $table = $this->table('files', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('relate_type', 'string', ['limit' => 50])
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
