<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInEvents extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('events');
        $table->renameColumn('event_id', 'id');
        $table->renameColumn('event_title', 'title');
        $table->renameColumn('event_text', 'text');
        $table->renameColumn('event_author', 'author');
        $table->renameColumn('event_image', 'image');
        $table->renameColumn('event_time', 'time');
        $table->renameColumn('event_comments', 'comments');
        $table->renameColumn('event_closed', 'closed');
        $table->renameColumn('event_top', 'top');
    }
}
