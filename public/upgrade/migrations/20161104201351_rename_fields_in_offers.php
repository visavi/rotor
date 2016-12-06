<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInOffers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('offers');
        $table->renameColumn('offers_id', 'id');
        $table->renameColumn('offers_type', 'type');
        $table->renameColumn('offers_title', 'title');
        $table->renameColumn('offers_text', 'text');
        $table->renameColumn('offers_user', 'user');
        $table->renameColumn('offers_votes', 'votes');
        $table->renameColumn('offers_time', 'time');
        $table->renameColumn('offers_status', 'status');
        $table->renameColumn('offers_comments', 'comments');
        $table->renameColumn('offers_closed', 'closed');
        $table->renameColumn('offers_text_reply', 'text_reply');
        $table->renameColumn('offers_user_reply', 'user_reply');
        $table->renameColumn('offers_time_reply', 'time_reply');
    }
}
