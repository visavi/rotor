<?php

use Phinx\Migration\AbstractMigration;

class AddTypeToNotice extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('notice');
        $table->addColumn('type', 'string', ['limit' => 20, 'after' => 'id'])
            ->update();

        $this->execute('UPDATE notice SET type="register" WHERE id="1";');

        $table->addIndex('type', ['unique' => true])->update();
    }
}
