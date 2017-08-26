<?php

use Phinx\Migration\AbstractMigration;

class DeleteEventsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('events');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('events')) {
            $table = $this->table('events', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('author', 'string', ['limit' => 20])
                ->addColumn('image', 'string', ['limit' => 30, 'null' => true])
                ->addColumn('time', 'integer')
                ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('closed', 'boolean', ['signed' => false, 'default' => 0])
                ->addColumn('top', 'boolean', ['default' => 0])
                ->addIndex('time')
                ->create();
        }
    }
}
