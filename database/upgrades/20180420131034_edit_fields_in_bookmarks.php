<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInBookmarks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bookmarks');
        $table
            ->changeColumn('topic_id', 'integer')
            ->changeColumn('count_posts', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
