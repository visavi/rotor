<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserIdInError extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('error');
        $table
            ->changeColumn('user_id', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('error');
        $table
            ->changeColumn('user_id', 'integer')
            ->save();
    }
}
