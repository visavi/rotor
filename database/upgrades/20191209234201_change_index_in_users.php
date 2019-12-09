<?php

use Phinx\Migration\AbstractMigration;

class ChangeIndexInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('users');
        $table
            ->removeIndexByName('themes')
            ->removeIndexByName('email')
            ->save();

        $table
            ->addIndex('email', ['unique' => true])
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('users');
        $table
            ->removeIndexByName('created_at')
            ->addIndex('themes')
            ->save();
    }
}
