<?php

use Phinx\Migration\AbstractMigration;

class CreatePollingsTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('pollings');
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('relate_type', 'enum', ['values' => ['blog', 'down', 'offer', 'gallery']])
            ->addColumn('relate_id', 'integer', ['signed' => false])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['relate_type', 'relate_id', 'user'])
            ->create();
    }
}
