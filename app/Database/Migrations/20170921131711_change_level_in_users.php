<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeLevelInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('level', 'string', ['limit' => 20, 'default' => 'guest'])
            ->save();

        $this->execute('UPDATE users SET level="admin" WHERE level="101";');
        $this->execute('UPDATE users SET level="admin" WHERE level="102";');
        $this->execute('UPDATE users SET level="moder" WHERE level="103";');
        $this->execute('UPDATE users SET level="editor" WHERE level="105";');
        $this->execute('UPDATE users SET level="user" WHERE level="107";');
        $this->execute('UPDATE users SET level="banned" WHERE ban="1";');
        $this->execute('UPDATE users SET level="pended" WHERE confirmreg="1";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('level', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 107])
            ->save();

        $this->execute('UPDATE users SET level="101" WHERE level="admin";');
        $this->execute('UPDATE users SET level="102" WHERE level="admin";');
        $this->execute('UPDATE users SET level="103" WHERE level="moder";');
        $this->execute('UPDATE users SET level="105" WHERE level="editor";');
        $this->execute('UPDATE users SET level="107" WHERE level="user";');
    }
}
