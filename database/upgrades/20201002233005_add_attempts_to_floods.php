<?php

use Phinx\Migration\AbstractMigration;

class AddAttemptsToFloods extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('floods');
        $table->removeColumn('user_id')
            ->addColumn('uid', 'string', ['limit' => 32, 'after' => 'id'])
            ->addColumn('attempts', 'integer', ['default' => 0, 'after' => 'page'])
            ->addIndex('uid')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('floods');
        $table->removeColumn('uid')
            ->removeColumn('attempts')
            ->addColumn('user_id', 'integer', ['null' => true, 'after' => 'id'])
            ->addIndex('user_id')
            ->save();
    }
}
