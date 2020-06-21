<?php

use Phinx\Migration\AbstractMigration;

class RenameCategoriesTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('categories');
        $table->rename('blogs')->update();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('blogs');
        $table->rename('categories')->update();
    }
}
