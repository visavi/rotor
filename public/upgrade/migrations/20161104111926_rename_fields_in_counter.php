<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCounter extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('counter');
        $table->renameColumn('count_id', 'id');
        $table->renameColumn('count_hours', 'hours');
        $table->renameColumn('count_days', 'days');
        $table->renameColumn('count_allhosts', 'allhosts');
        $table->renameColumn('count_allhits', 'allhits');
        $table->renameColumn('count_dayhosts', 'dayhosts');
        $table->renameColumn('count_dayhits', 'dayhits');
        $table->renameColumn('count_hosts24', 'hosts24');
        $table->renameColumn('count_hits24', 'hits24');
    }
}
