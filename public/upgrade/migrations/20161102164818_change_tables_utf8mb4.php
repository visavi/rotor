<?php

use Phinx\Migration\AbstractMigration;

class ChangeTablesUtf8mb4 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("ALTER DATABASE ".env('DB_DATABASE')." CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;");

        $tables = $this->fetchAll('SHOW FULL TABLES');
        foreach ($tables as $table) {
            $this->execute("ALTER TABLE `".$table[0]."` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("ALTER DATABASE " . env('DB_DATABASE') . " CHARACTER SET = utf8 COLLATE = utf8_general_ci;");

        $tables = $this->fetchAll('SHOW TABLES');
        foreach ($tables as $table) {
           $this->execute("ALTER TABLE `".$table[0]."` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;");
        }
    }
}
