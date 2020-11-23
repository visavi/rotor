<?php

use Phinx\Migration\AbstractMigration;

class CreateCommentsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('comments')) {
            $table = $this->table('comments', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('relate_type', 'string', ['limit' => 10])
                ->addColumn('relate_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('ip', 'string', ['limit' => 45])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addIndex(['relate_type', 'relate_id'], ['name' => 'relate_type'])
                ->addIndex('created_at')
                ->create();
        }
    }
}
