<?php

use Phinx\Migration\AbstractMigration;

class CreateErrorsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('errors')) {
            $table = $this->table('errors', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('code', 'integer')
                ->addColumn('request', 'string', ['null' => true])
                ->addColumn('referer', 'string', ['null' => true])
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('ip', 'string', ['limit' => 45])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addIndex(['code', 'created_at'], ['name' => 'code'])
                ->create();
        }
    }
}
