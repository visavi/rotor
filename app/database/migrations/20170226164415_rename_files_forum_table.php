<?php

use Phinx\Migration\AbstractMigration;

class RenameFilesForumTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('files_forum');
        $table->rename('files');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('files');
        $table->rename('files_forum');
    }
}
