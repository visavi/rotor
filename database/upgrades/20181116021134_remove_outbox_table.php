<?php

use Phinx\Migration\AbstractMigration;

class RemoveOutboxTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('outbox');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('outbox')) {
            $table = $this->table('outbox', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('recipient_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
