<?php

use Phinx\Migration\AbstractMigration;

class DeleteTrashTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('trash');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('trash')) {
            $table = $this->table('trash', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user_id', 'integer')
                ->addColumn('author_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addColumn('deleted_at', 'integer')
                ->addIndex('created_at')
                ->addIndex('user_id')
                ->create();
        }
    }
}
