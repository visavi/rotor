<?php

use Phinx\Migration\AbstractMigration;

class CreateSpamTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('spam')) {
            $table = $this->table('spam', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('relate_type', 'string', ['limit' => 50])
                ->addColumn('relate_id', 'integer')
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addColumn('path', 'string', ['limit' => 100, 'null' => true])
                ->addIndex('created_at')
                ->addIndex(['relate_type', 'relate_id'], ['name' => 'relate_type'])
                ->create();
        }
    }
}
