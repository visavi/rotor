<?php

use Phinx\Migration\AbstractMigration;

class AddRecaptchaToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('recaptcha_public', '');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('recaptcha_private', '');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='recaptcha_public' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='recaptcha_private' LIMIT 1;");
    }
}
