<?php

use Phinx\Migration\AbstractMigration;

class DeleteLotusersTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('lotusers');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('lotusers')) {
            $table = $this->table('lotusers', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user_id', 'integer')
                ->addColumn('num', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->create();
        }
    }
}
