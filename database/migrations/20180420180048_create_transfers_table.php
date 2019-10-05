<?php

use Phinx\Migration\AbstractMigration;

class CreateTransfersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('transfers')) {
            $table = $this->table('transfers', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('recipient_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('total', 'integer', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->addIndex('recipient_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
