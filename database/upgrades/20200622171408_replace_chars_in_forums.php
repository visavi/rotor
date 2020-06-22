<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInForums extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE forums SET title = replace(`title`, '&amp;', '&')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&quot;', '\"')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#039;', \"'\")");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#39;', \"'\")");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#36;', '$')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#92;', '\\\')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#124;', '|')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#94;', '^')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#96;', '`')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#37;', '%')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#58;', ':')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&#64;', '@')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&lt;', '<')");
        $this->execute("UPDATE forums SET title = replace(`title`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE forums SET title = replace(`title`, '&', '&amp;')");
        $this->execute("UPDATE forums SET title = replace(`title`, '\"', '&quot;')");
        $this->execute("UPDATE forums SET title = replace(`title`, \"'\", '&#039;')");
        $this->execute("UPDATE forums SET title = replace(`title`, '<', '&lt;')");
        $this->execute("UPDATE forums SET title = replace(`title`, '>', '&gt;')");
    }
}
