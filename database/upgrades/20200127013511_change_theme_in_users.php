<?php

use Phinx\Migration\AbstractMigration;

class ChangeThemeInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE users SET themes='mobile' WHERE themes='default';");
        $this->execute("UPDATE users SET themes='default' WHERE themes NOT IN('mobile', 'motor');");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {

    }
}
