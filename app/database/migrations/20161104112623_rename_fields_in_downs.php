<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInDowns extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('downs');
        $table->renameColumn('downs_id', 'id');
        $table->renameColumn('downs_cats_id', 'cats_id');
        $table->renameColumn('downs_title', 'title');
        $table->renameColumn('downs_text', 'text');
        $table->renameColumn('downs_link', 'link');
        $table->renameColumn('downs_user', 'user');
        $table->renameColumn('downs_author', 'author');
        $table->renameColumn('downs_site', 'site');
        $table->renameColumn('downs_screen', 'screen');
        $table->renameColumn('downs_time', 'time');
        $table->renameColumn('downs_comments', 'comments');
        $table->renameColumn('downs_raiting', 'raiting');
        $table->renameColumn('downs_rated', 'rated');
        $table->renameColumn('downs_load', 'load');
        $table->renameColumn('downs_last_load', 'last_load');
        $table->renameColumn('downs_app', 'app');
        $table->renameColumn('downs_notice', 'notice');
        $table->renameColumn('downs_active', 'active');
    }
}
