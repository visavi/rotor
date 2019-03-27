<?php

use Phinx\Migration\AbstractMigration;

class AddDisabledToModules extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change(): void
    {
        $table = $this->table('modules');
        $table->addColumn('disabled', 'boolean', ['default' => 0, 'after' => 'version'])
            ->update();
    }
}
