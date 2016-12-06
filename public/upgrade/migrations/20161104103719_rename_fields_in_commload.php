<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCommload extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('commload');
        $table->renameColumn('commload_id', 'id');
        $table->renameColumn('commload_cats', 'cats');
        $table->renameColumn('commload_down', 'down');
        $table->renameColumn('commload_text', 'text');
        $table->renameColumn('commload_author', 'author');
        $table->renameColumn('commload_time', 'time');
        $table->renameColumn('commload_ip', 'ip');
        $table->renameColumn('commload_brow', 'brow');
    }
}
