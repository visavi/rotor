<?php

use Phinx\Migration\AbstractMigration;

class DeleteCommloadTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('commload');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `commload` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `cats` smallint(4) unsigned NOT NULL,
          `down` mediumint(8) unsigned NOT NULL,
          `text` text DEFAULT NULL,
          `author` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL DEFAULT '0',
          `ip` varchar(20) NOT NULL,
          `brow` varchar(25) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `down` (`down`),
          KEY `time` (`time`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
