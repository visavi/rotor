<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInFilesForum extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('files_forum');
        $table->renameColumn('file_id', 'id');
        $table->renameColumn('file_topics_id', 'topics_id');
        $table->renameColumn('file_posts_id', 'posts_id');
        $table->renameColumn('file_hash', 'hash');
        $table->renameColumn('file_name', 'name');
        $table->renameColumn('file_size', 'size');
        $table->renameColumn('file_user', 'user');
        $table->renameColumn('file_time', 'time');
    }
}
