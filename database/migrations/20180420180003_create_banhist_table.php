<?php

use Phinx\Migration\AbstractMigration;

class CreateBanhistTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('banhist')) {
            $table = $this->table('banhist', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('send_user_id', 'integer')
                ->addColumn('type', 'enum', ['values' => ['ban','unban','change']])
                ->addColumn('reason', 'text', ['null' => true])
                ->addColumn('term', 'integer', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addColumn('explain', 'boolean', ['default' => 0])
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
