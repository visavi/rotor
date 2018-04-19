<?php

use Phinx\Migration\AbstractMigration;

class RenameUserIdIndexInBookmarks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bookmarks');
        $table
            ->removeIndex('user_id')
            ->addIndex('user_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
