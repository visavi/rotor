<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInRating extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('rating');
        $table->renameColumn('rating_id', 'id');
        $table->renameColumn('rating_user', 'user');
        $table->renameColumn('rating_login', 'login');
        $table->renameColumn('rating_text', 'text');
        $table->renameColumn('rating_vote', 'vote');
        $table->renameColumn('rating_time', 'time');
    }
}
