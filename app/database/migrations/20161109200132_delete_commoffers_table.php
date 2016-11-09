<?php

use Phinx\Migration\AbstractMigration;

class DeleteCommoffersTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('commoffers');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `commoffers` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `offers` smallint(4) unsigned NOT NULL,
          `text` text DEFAULT NULL,
          `user` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL DEFAULT '0',
          `ip` varchar(20) NOT NULL,
          `brow` varchar(25) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `offers` (`offers`),
          KEY `time` (`time`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
