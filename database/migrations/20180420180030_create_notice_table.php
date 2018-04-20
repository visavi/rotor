<?php

use Phinx\Migration\AbstractMigration;

class CreateNoticeTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('notice')) {
            $table = $this->table('notice', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('type', 'string', ['limit' => 20])
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addColumn('updated_at', 'integer')
                ->addColumn('protect', 'boolean', ['default' => 0])
                ->addIndex('type', ['unique' => true])
                ->create();
        }
    }
}
