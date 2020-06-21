<?php

use Phinx\Migration\AbstractMigration;

class RenameBlogsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('blogs');
        $table->rename('articles')->update();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('articles');
        $table->rename('blogs')->update();
    }
}
