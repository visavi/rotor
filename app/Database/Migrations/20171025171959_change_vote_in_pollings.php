<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeVoteInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('pollings');
        $table
            ->changeColumn('vote', 'string', ['limit' => 5])
            ->save();

        $this->execute('UPDATE pollings SET vote="+" WHERE vote = "1";');
        $this->execute('UPDATE pollings SET vote="-" WHERE vote = "-1";');

        $table
            ->changeColumn('vote', 'string', ['limit' => 1])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('pollings');

        $table
            ->changeColumn('vote', 'string', ['limit' => 5])
            ->save();

        $this->execute('UPDATE pollings SET vote="1" WHERE vote = "+";');
        $this->execute('UPDATE pollings SET vote="-1" WHERE vote = "-";');

        $table
            ->changeColumn('vote', 'boolean', ['default' => true])
            ->save();
    }
}
