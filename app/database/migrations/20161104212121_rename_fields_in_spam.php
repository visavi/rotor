<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInSpam extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('spam');
        $table->renameColumn('spam_id', 'id');
        $table->renameColumn('spam_key', 'key');
        $table->renameColumn('spam_idnum', 'idnum');
        $table->renameColumn('spam_user', 'user');
        $table->renameColumn('spam_login', 'login');
        $table->renameColumn('spam_text', 'text');
        $table->renameColumn('spam_time', 'time');
        $table->renameColumn('spam_addtime', 'addtime');
        $table->renameColumn('spam_link', 'link');
    }
}
