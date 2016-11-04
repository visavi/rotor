<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCommevents extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('commevents');
        $table->renameColumn('commevent_id', 'id');
        $table->renameColumn('commevent_event_id', 'event_id');
        $table->renameColumn('commevent_text', 'text');
        $table->renameColumn('commevent_author', 'author');
        $table->renameColumn('commevent_time', 'time');
        $table->renameColumn('commevent_ip', 'ip');
        $table->renameColumn('commevent_brow', 'brow');
    }
}
