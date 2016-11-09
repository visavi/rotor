<?php

use Phinx\Migration\AbstractMigration;

class DeleteCommnewsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('commnews');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `commnews` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `news_id` mediumint(8) unsigned NOT NULL,
          `text` text DEFAULT NULL,
          `author` varchar(20) NOT NULL,
          `time` int(11) unsigned NOT NULL,
          `ip` varchar(20) NOT NULL,
          `brow` varchar(25) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `news_id` (`news_id`),
          KEY `time` (`time`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
    }
}
