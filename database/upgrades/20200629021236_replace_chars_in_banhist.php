<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&amp;', '&')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&quot;', '\"')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#039;', \"'\")");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#39;', \"'\")");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#36;', '$')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#92;', '\\\')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#124;', '|')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#94;', '^')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#96;', '`')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#37;', '%')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#58;', ':')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&#64;', '@')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&lt;', '<')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '&', '&amp;')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '\"', '&quot;')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, \"'\", '&#039;')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '<', '&lt;')");
        $this->execute("UPDATE banhist SET reason = replace(`reason`, '>', '&gt;')");
    }
}
