<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInBank extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bank');
        if ($table->exists()) {
            $users = $this->table('bank');
            $users->changeColumn('time', 'integer')
                ->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
