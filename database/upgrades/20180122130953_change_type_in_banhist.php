<?php

use Phinx\Migration\AbstractMigration;

class ChangeTypeInBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('banhist');
        $table
            ->changeColumn('type', 'string')
            ->save();

        $this->execute('UPDATE banhist SET type="unban" WHERE type = "0";');
        $this->execute('UPDATE banhist SET type="ban" WHERE type = "1";');
        $this->execute('UPDATE banhist SET type="change" WHERE type = "2";');

        $table
            ->changeColumn('type', 'enum', ['values' => ['ban', 'unban', 'change']])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('banhist');
        $table
            ->changeColumn('type', 'string')
            ->save();

        $this->execute('UPDATE banhist SET type="0" WHERE type = "unban";');
        $this->execute('UPDATE banhist SET type="1" WHERE type = "ban";');
        $this->execute('UPDATE banhist SET type="2" WHERE type = "change";');

        $table
            ->changeColumn('type', 'boolean', ['default' => false])
            ->save();
    }
}
