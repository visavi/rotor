<?php

use Phinx\Migration\AbstractMigration;

class DeleteCommblogTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('commblog');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `commblog` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `cats` smallint(4) unsigned NOT NULL,
          `blog` int(11) unsigned NOT NULL,
          `text` text DEFAULT NULL,
          `author` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL DEFAULT '0',
          `ip` varchar(20) NOT NULL,
          `brow` varchar(25) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `blog` (`blog`),
          KEY `time` (`time`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
