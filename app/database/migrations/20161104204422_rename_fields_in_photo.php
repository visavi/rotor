<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInPhoto extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('photo');
        $table->renameColumn('photo_id', 'id');
        $table->renameColumn('photo_user', 'user');
        $table->renameColumn('photo_title', 'title');
        $table->renameColumn('photo_text', 'text');
        $table->renameColumn('photo_link', 'link');
        $table->renameColumn('photo_time', 'time');
        $table->renameColumn('photo_rating', 'rating');
        $table->renameColumn('photo_closed', 'closed');
        $table->renameColumn('photo_comments', 'comments');
    }
}
