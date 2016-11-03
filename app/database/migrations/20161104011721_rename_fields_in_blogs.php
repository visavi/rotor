<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBlogs extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('blogs');
        $table->renameColumn('blogs_id', 'id');
        $table->renameColumn('blogs_cats_id', 'cats_id');
        $table->renameColumn('blogs_user', 'user');
        $table->renameColumn('blogs_title', 'title');
        $table->renameColumn('blogs_text', 'text');
        $table->renameColumn('blogs_tags', 'tags');
        $table->renameColumn('blogs_rating', 'rating');
        $table->renameColumn('blogs_read', 'read');
        $table->renameColumn('blogs_comments', 'comments');
        $table->renameColumn('blogs_time', 'time');
    }
}
