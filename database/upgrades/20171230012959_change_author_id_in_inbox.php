<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeAuthorIdInInbox extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('inbox');
        $table
            ->changeColumn('author_id', 'integer', ['null' => true])
            ->save();

        $this->execute('UPDATE inbox SET author_id=null WHERE author_id = "0";');

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('inbox');
        $table
            ->changeColumn('author_id', 'integer')
            ->save();

        $this->execute('UPDATE inbox SET author_id = 0 WHERE author_id IS NULL;');
    }
}
