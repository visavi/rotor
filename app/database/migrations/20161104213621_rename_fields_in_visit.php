<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInVisit extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('visit');
        $table->renameColumn('visit_id', 'id');
        $table->renameColumn('visit_user', 'user');
        $table->renameColumn('visit_self', 'self');
        $table->renameColumn('visit_page', 'page');
        $table->renameColumn('visit_ip', 'ip');
        $table->renameColumn('visit_count', 'count');
        $table->renameColumn('visit_nowtime', 'nowtime');
    }
}
