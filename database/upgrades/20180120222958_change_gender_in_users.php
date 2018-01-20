<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeGenderInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('gender', 'string')
            ->save();

        $this->execute('UPDATE users SET gender="male" WHERE gender = "1";');
        $this->execute('UPDATE users SET gender="female" WHERE gender = "2";');

        $table
            ->changeColumn('gender', 'enum', ['values' => ['male', 'female']])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('gender', 'string')
            ->save();

        $this->execute('UPDATE users SET gender="1" WHERE gender = "male";');
        $this->execute('UPDATE users SET gender="2" WHERE gender = "female";');

        $table
            ->changeColumn('gender', 'boolean', ['default' => false])
            ->save();
    }
}
