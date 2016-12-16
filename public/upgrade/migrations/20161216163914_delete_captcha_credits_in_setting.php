<?php

use Phinx\Migration\AbstractMigration;

class DeleteCaptchaCreditsInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='captcha_credits' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('captcha_credits', 0);");
    }
}
