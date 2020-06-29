<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInAdminAdverts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&amp;', '&')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&quot;', '\"')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#039;', \"'\")");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#39;', \"'\")");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#36;', '$')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#92;', '\\\')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#124;', '|')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#94;', '^')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#96;', '`')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#37;', '%')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#58;', ':')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&#64;', '@')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&lt;', '<')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '&', '&amp;')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '\"', '&quot;')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, \"'\", '&#039;')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '<', '&lt;')");
        $this->execute("UPDATE admin_adverts SET name = replace(`name`, '>', '&gt;')");
    }
}
