<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInVoteanswer extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&amp;', '&')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&quot;', '\"')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#039;', \"'\")");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#39;', \"'\")");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#36;', '$')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#92;', '\\\')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#124;', '|')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#94;', '^')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#96;', '`')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#37;', '%')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#58;', ':')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&#64;', '@')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&lt;', '<')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '&', '&amp;')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '\"', '&quot;')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, \"'\", '&#039;')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '<', '&lt;')");
        $this->execute("UPDATE voteanswer SET answer = replace(`answer`, '>', '&gt;')");
    }
}
