<?php

use Phinx\Migration\AbstractMigration;

class DeleteNavigationTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('navigation');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `navigation` (
          `nav_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `nav_url` varchar(100) NOT NULL,
          `nav_title` varchar(100) NOT NULL,
          `nav_order` smallint(4) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`nav_id`)
        ) ENGINE=InnoDb  DEFAULT CHARSET=utf8mb4;");
    }
}
