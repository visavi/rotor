<?php

use Phinx\Migration\AbstractMigration;

class CreateMailingsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('mailings')) {
            $table = $this->table('mailings', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('type', 'string', ['limit' => 30])
                ->addColumn('subject', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('sent', 'boolean', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addColumn('sent_at', 'integer', ['null' => true])
                ->create();
        }
    }
}
