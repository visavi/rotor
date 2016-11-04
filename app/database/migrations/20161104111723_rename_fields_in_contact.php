<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInContact extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('contact');
        $table->renameColumn('contact_id', 'id');
        $table->renameColumn('contact_user', 'user');
        $table->renameColumn('contact_name', 'name');
        $table->renameColumn('contact_text', 'text');
        $table->renameColumn('contact_time', 'time');
    }
}
