<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class DeleteLoadsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('loads');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('loads')) {
            $table = $this->table('loads', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('down_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('created_at', 'integer')
                ->create();
        }
    }
}
