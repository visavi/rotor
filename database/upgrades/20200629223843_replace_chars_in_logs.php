<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInLogs extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE logs SET request = replace(`request`, '&amp;', '&')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&quot;', '\"')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#039;', \"'\")");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#39;', \"'\")");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#36;', '$')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#92;', '\\\')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#124;', '|')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#94;', '^')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#96;', '`')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#37;', '%')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#58;', ':')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&#64;', '@')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&lt;', '<')");
        $this->execute("UPDATE logs SET request = replace(`request`, '&gt;', '>')");

        $this->execute("UPDATE logs SET referer = replace(`referer`, '&amp;', '&')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&quot;', '\"')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#039;', \"'\")");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#39;', \"'\")");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#36;', '$')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#92;', '\\\')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#124;', '|')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#94;', '^')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#96;', '`')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#37;', '%')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#58;', ':')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&#64;', '@')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&lt;', '<')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE logs SET request = replace(`request`, '&', '&amp;')");
        $this->execute("UPDATE logs SET request = replace(`request`, '\"', '&quot;')");
        $this->execute("UPDATE logs SET request = replace(`request`, \"'\", '&#039;')");
        $this->execute("UPDATE logs SET request = replace(`request`, '<', '&lt;')");
        $this->execute("UPDATE logs SET request = replace(`request`, '>', '&gt;')");

        $this->execute("UPDATE logs SET referer = replace(`referer`, '&', '&amp;')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '\"', '&quot;')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, \"'\", '&#039;')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '<', '&lt;')");
        $this->execute("UPDATE logs SET referer = replace(`referer`, '>', '&gt;')");
    }
}
