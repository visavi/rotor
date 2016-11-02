<?php

use Phinx\Migration\AbstractMigration;

class DeleteAdvertTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('advert');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `advert` (
          `adv_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `adv_url` varchar(100) NOT NULL,
          `adv_title` varchar(100) NOT NULL,
          `adv_color` varchar(10) NOT NULL DEFAULT '',
          `adv_user` varchar(20) NOT NULL,
          PRIMARY KEY (`adv_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }
}
