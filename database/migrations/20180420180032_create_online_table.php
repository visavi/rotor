<?php

use Phinx\Migration\AbstractMigration;

class CreateOnlineTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('online')) {
            $table = $this->table('online', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addColumn('user_id', 'integer', ['null' => true])
                ->create();
        }
    }
}
