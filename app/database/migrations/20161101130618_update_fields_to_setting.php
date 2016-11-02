<?php

use Phinx\Migration\AbstractMigration;

class UpdateFieldsToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('captcha_offset', 5);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('captcha_distortion', 1);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('captcha_interpolation', 1);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('captcha_angle', 20);");
        $this->execute("DELETE FROM setting WHERE setting_name='captcha_noise' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='captcha_amplitude' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('captcha_noise', 1);");
        $this->execute("INSERT INTO setting (setting_name, setting_value) VALUES ('captcha_amplitude', 5);");
        $this->execute("DELETE FROM setting WHERE setting_name='captcha_offset' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='captcha_distortion' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='captcha_interpolation' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE setting_name='captcha_angle' LIMIT 1;");
    }
}
