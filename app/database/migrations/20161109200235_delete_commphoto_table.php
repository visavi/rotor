<?php

use Phinx\Migration\AbstractMigration;

class DeleteCommphotoTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('commphoto');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `commphoto` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `gid` mediumint(8) unsigned NOT NULL,
          `text` text DEFAULT NULL,
          `user` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL DEFAULT '0',
          `ip` varchar(20) NOT NULL,
          `brow` varchar(25) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `gid` (`gid`),
          KEY `time` (`time`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
