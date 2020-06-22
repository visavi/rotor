<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE topics SET title = replace(`title`, '&amp;', '&')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&quot;', '\"')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#039;', \"'\")");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#39;', \"'\")");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#36;', '$')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#92;', '\\\')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#124;', '|')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#94;', '^')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#96;', '`')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#37;', '%')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#58;', ':')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&#64;', '@')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&lt;', '<')");
        $this->execute("UPDATE topics SET title = replace(`title`, '&gt;', '>')");

        $this->execute("UPDATE topics SET note = replace(`note`, '&amp;', '&')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&quot;', '\"')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#039;', \"'\")");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#39;', \"'\")");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#36;', '$')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#92;', '\\\')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#124;', '|')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#94;', '^')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#96;', '`')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#37;', '%')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#58;', ':')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&#64;', '@')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&lt;', '<')");
        $this->execute("UPDATE topics SET note = replace(`note`, '&gt;', '>')");

        $this->execute("UPDATE topics SET note = replace(`note`, '<br />', \"\r\n\")");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE topics SET title = replace(`title`, '&', '&amp;')");
        $this->execute("UPDATE topics SET title = replace(`title`, '\"', '&quot;')");
        $this->execute("UPDATE topics SET title = replace(`title`, \"'\", '&#039;')");
        $this->execute("UPDATE topics SET title = replace(`title`, '<', '&lt;')");
        $this->execute("UPDATE topics SET title = replace(`title`, '>', '&gt;')");

        $this->execute("UPDATE topics SET note = replace(`note`, '&', '&amp;')");
        $this->execute("UPDATE topics SET note = replace(`note`, '\"', '&quot;')");
        $this->execute("UPDATE topics SET note = replace(`note`, \"'\", '&#039;')");
        $this->execute("UPDATE topics SET note = replace(`note`, '<', '&lt;')");
        $this->execute("UPDATE topics SET note = replace(`note`, '>', '&gt;')");
    }
}
