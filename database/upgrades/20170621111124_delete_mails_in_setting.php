<?php

use Phinx\Migration\AbstractMigration;

class DeleteMailsInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='maildriver' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='mailhost' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='mailpassword' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='mailport' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='mailsecurity' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='mailusername' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='sendmail' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('maildriver', 'sendmail');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('mailhost', 'smtp.yandex.ru');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('mailpassword', '');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('mailport', 465);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('mailsecurity', 'ssl');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('mailusername', '');");
    }
}
