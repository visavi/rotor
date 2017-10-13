<?php

use Phinx\Migration\AbstractMigration;

class DeleteVisitTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('visit');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('visit')) {
            $table = $this->table('visit', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user_id', 'integer')
                ->addColumn('self', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('page', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('count', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('updated_at', 'integer', ['default' => 0])
                ->addIndex('user_id', ['unique' => true])
                ->addIndex('updated_at')
                ->create();
        }
    }
}
