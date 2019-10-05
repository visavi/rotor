<?php

use Phinx\Migration\AbstractMigration;

class CreateIgnoringTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('ignoring')) {
            $table = $this->table('ignoring', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('ignore_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
