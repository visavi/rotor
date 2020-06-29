<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE downs SET title = replace(`title`, '&amp;', '&')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&quot;', '\"')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#039;', \"'\")");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#39;', \"'\")");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#36;', '$')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#92;', '\\\')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#124;', '|')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#94;', '^')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#96;', '`')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#37;', '%')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#58;', ':')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&#64;', '@')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&lt;', '<')");
        $this->execute("UPDATE downs SET title = replace(`title`, '&gt;', '>')");

        $this->execute("UPDATE downs SET text = replace(`text`, '&amp;', '&')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&quot;', '\"')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#039;', \"'\")");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#39;', \"'\")");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#36;', '$')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#92;', '\\\')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#124;', '|')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#94;', '^')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#96;', '`')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#37;', '%')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#58;', ':')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&#64;', '@')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&lt;', '<')");
        $this->execute("UPDATE downs SET text = replace(`text`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE downs SET title = replace(`title`, '&', '&amp;')");
        $this->execute("UPDATE downs SET title = replace(`title`, '\"', '&quot;')");
        $this->execute("UPDATE downs SET title = replace(`title`, \"'\", '&#039;')");
        $this->execute("UPDATE downs SET title = replace(`title`, '<', '&lt;')");
        $this->execute("UPDATE downs SET title = replace(`title`, '>', '&gt;')");

        $this->execute("UPDATE downs SET text = replace(`text`, '&', '&amp;')");
        $this->execute("UPDATE downs SET text = replace(`text`, '\"', '&quot;')");
        $this->execute("UPDATE downs SET text = replace(`text`, \"'\", '&#039;')");
        $this->execute("UPDATE downs SET text = replace(`text`, '<', '&lt;')");
        $this->execute("UPDATE downs SET text = replace(`text`, '>', '&gt;')");
    }
}
