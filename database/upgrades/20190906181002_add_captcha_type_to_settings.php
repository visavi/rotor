<?php

use Phinx\Migration\AbstractMigration;

class AddCaptchaTypeToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('captcha_type', 'graphical');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM settings WHERE name='captcha_type' LIMIT 1;");
    }
}
