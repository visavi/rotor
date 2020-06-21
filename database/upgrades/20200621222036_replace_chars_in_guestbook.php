<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInGuestbook extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE guestbook SET text = replace(`text`, '&amp;', '&')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '&quot;', '\"')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '&#039;', \"'\")");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '&lt;', '<')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE guestbook SET text = replace(`text`, '&', '&amp;')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '\"', '&quot;')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, \"'\", '&#039;')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '<', '&lt;')");
        $this->execute("UPDATE guestbook SET text = replace(`text`, '>', '&gt;')");
    }
}
