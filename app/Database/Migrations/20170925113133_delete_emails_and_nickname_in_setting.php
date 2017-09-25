<?php

use Phinx\Migration\AbstractMigration;

class DeleteEmailsAndNicknameInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $setting = $this->fetchRow("SELECT value FROM setting WHERE name = 'nickname'");

        $this->execute("UPDATE users SET level='boss' WHERE login = '".$setting['value']."' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='emails' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='nickname' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('emails', 'admin@site.ru');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('nickname', 'admin');");
    }
}
