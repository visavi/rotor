<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInRating extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE rating SET text = replace(`text`, '&amp;', '&')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&quot;', '\"')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#039;', \"'\")");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#39;', \"'\")");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#36;', '$')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#92;', '\\\')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#124;', '|')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#94;', '^')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#96;', '`')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#37;', '%')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#58;', ':')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&#64;', '@')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&lt;', '<')");
        $this->execute("UPDATE rating SET text = replace(`text`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE rating SET text = replace(`text`, '&', '&amp;')");
        $this->execute("UPDATE rating SET text = replace(`text`, '\"', '&quot;')");
        $this->execute("UPDATE rating SET text = replace(`text`, \"'\", '&#039;')");
        $this->execute("UPDATE rating SET text = replace(`text`, '<', '&lt;')");
        $this->execute("UPDATE rating SET text = replace(`text`, '>', '&gt;')");
    }
}
