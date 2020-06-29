<?php

use Phinx\Migration\AbstractMigration;

class ReplaceCharsInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE users SET name = replace(`name`, '&amp;', '&')");
        $this->execute("UPDATE users SET name = replace(`name`, '&quot;', '\"')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#039;', \"'\")");
        $this->execute("UPDATE users SET name = replace(`name`, '&#39;', \"'\")");
        $this->execute("UPDATE users SET name = replace(`name`, '&#36;', '$')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#92;', '\\\')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#124;', '|')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#94;', '^')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#96;', '`')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#37;', '%')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#58;', ':')");
        $this->execute("UPDATE users SET name = replace(`name`, '&#64;', '@')");
        $this->execute("UPDATE users SET name = replace(`name`, '&lt;', '<')");
        $this->execute("UPDATE users SET name = replace(`name`, '&gt;', '>')");

        $this->execute("UPDATE users SET country = replace(`country`, '&amp;', '&')");
        $this->execute("UPDATE users SET country = replace(`country`, '&quot;', '\"')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#039;', \"'\")");
        $this->execute("UPDATE users SET country = replace(`country`, '&#39;', \"'\")");
        $this->execute("UPDATE users SET country = replace(`country`, '&#36;', '$')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#92;', '\\\')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#124;', '|')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#94;', '^')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#96;', '`')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#37;', '%')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#58;', ':')");
        $this->execute("UPDATE users SET country = replace(`country`, '&#64;', '@')");
        $this->execute("UPDATE users SET country = replace(`country`, '&lt;', '<')");
        $this->execute("UPDATE users SET country = replace(`country`, '&gt;', '>')");

        $this->execute("UPDATE users SET city = replace(`city`, '&amp;', '&')");
        $this->execute("UPDATE users SET city = replace(`city`, '&quot;', '\"')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#039;', \"'\")");
        $this->execute("UPDATE users SET city = replace(`city`, '&#39;', \"'\")");
        $this->execute("UPDATE users SET city = replace(`city`, '&#36;', '$')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#92;', '\\\')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#124;', '|')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#94;', '^')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#96;', '`')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#37;', '%')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#58;', ':')");
        $this->execute("UPDATE users SET city = replace(`city`, '&#64;', '@')");
        $this->execute("UPDATE users SET city = replace(`city`, '&lt;', '<')");
        $this->execute("UPDATE users SET city = replace(`city`, '&gt;', '>')");

        $this->execute("UPDATE users SET info = replace(`info`, '&amp;', '&')");
        $this->execute("UPDATE users SET info = replace(`info`, '&quot;', '\"')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#039;', \"'\")");
        $this->execute("UPDATE users SET info = replace(`info`, '&#39;', \"'\")");
        $this->execute("UPDATE users SET info = replace(`info`, '&#36;', '$')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#92;', '\\\')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#124;', '|')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#94;', '^')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#96;', '`')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#37;', '%')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#58;', ':')");
        $this->execute("UPDATE users SET info = replace(`info`, '&#64;', '@')");
        $this->execute("UPDATE users SET info = replace(`info`, '&lt;', '<')");
        $this->execute("UPDATE users SET info = replace(`info`, '&gt;', '>')");

        $this->execute("UPDATE users SET status = replace(`status`, '&amp;', '&')");
        $this->execute("UPDATE users SET status = replace(`status`, '&quot;', '\"')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#039;', \"'\")");
        $this->execute("UPDATE users SET status = replace(`status`, '&#39;', \"'\")");
        $this->execute("UPDATE users SET status = replace(`status`, '&#36;', '$')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#92;', '\\\')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#124;', '|')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#94;', '^')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#96;', '`')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#37;', '%')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#58;', ':')");
        $this->execute("UPDATE users SET status = replace(`status`, '&#64;', '@')");
        $this->execute("UPDATE users SET status = replace(`status`, '&lt;', '<')");
        $this->execute("UPDATE users SET status = replace(`status`, '&gt;', '>')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE users SET name = replace(`name`, '&', '&amp;')");
        $this->execute("UPDATE users SET name = replace(`name`, '\"', '&quot;')");
        $this->execute("UPDATE users SET name = replace(`name`, \"'\", '&#039;')");
        $this->execute("UPDATE users SET name = replace(`name`, '<', '&lt;')");
        $this->execute("UPDATE users SET name = replace(`name`, '>', '&gt;')");

        $this->execute("UPDATE users SET country = replace(`country`, '&', '&amp;')");
        $this->execute("UPDATE users SET country = replace(`country`, '\"', '&quot;')");
        $this->execute("UPDATE users SET country = replace(`country`, \"'\", '&#039;')");
        $this->execute("UPDATE users SET country = replace(`country`, '<', '&lt;')");
        $this->execute("UPDATE users SET country = replace(`country`, '>', '&gt;')");

        $this->execute("UPDATE users SET city = replace(`city`, '&', '&amp;')");
        $this->execute("UPDATE users SET city = replace(`city`, '\"', '&quot;')");
        $this->execute("UPDATE users SET city = replace(`city`, \"'\", '&#039;')");
        $this->execute("UPDATE users SET city = replace(`city`, '<', '&lt;')");
        $this->execute("UPDATE users SET city = replace(`city`, '>', '&gt;')");

        $this->execute("UPDATE users SET info = replace(`info`, '&', '&amp;')");
        $this->execute("UPDATE users SET info = replace(`info`, '\"', '&quot;')");
        $this->execute("UPDATE users SET info = replace(`info`, \"'\", '&#039;')");
        $this->execute("UPDATE users SET info = replace(`info`, '<', '&lt;')");
        $this->execute("UPDATE users SET info = replace(`info`, '>', '&gt;')");

        $this->execute("UPDATE users SET status = replace(`status`, '&', '&amp;')");
        $this->execute("UPDATE users SET status = replace(`status`, '\"', '&quot;')");
        $this->execute("UPDATE users SET status = replace(`status`, \"'\", '&#039;')");
        $this->execute("UPDATE users SET status = replace(`status`, '<', '&lt;')");
        $this->execute("UPDATE users SET status = replace(`status`, '>', '&gt;')");
    }
}
