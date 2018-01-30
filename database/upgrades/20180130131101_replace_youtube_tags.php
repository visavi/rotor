<?php

use Phinx\Migration\AbstractMigration;

class ReplaceYoutubeTags extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE guest SET text = replace(`text`, '[youtube]https://www.youtube.com/watch?v=', '[youtube]')");
        $this->execute("UPDATE guest SET text = replace(`text`, '[youtube]', '[youtube]https://www.youtube.com/watch?v=')");

        $this->execute("UPDATE comments SET text = replace(`text`, '[youtube]https://www.youtube.com/watch?v=', '[youtube]')");
        $this->execute("UPDATE comments SET text = replace(`text`, '[youtube]', '[youtube]https://www.youtube.com/watch?v=')");

        $this->execute("UPDATE posts SET text = replace(`text`, '[youtube]https://www.youtube.com/watch?v=', '[youtube]')");
        $this->execute("UPDATE posts SET text = replace(`text`, '[youtube]', '[youtube]https://www.youtube.com/watch?v=')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("UPDATE guest SET text = replace(`text`, '[youtube]https://www.youtube.com/watch?v=', '[youtube]')");
        $this->execute("UPDATE comments SET text = replace(`text`, '[youtube]https://www.youtube.com/watch?v=', '[youtube]')");
        $this->execute("UPDATE posts SET text = replace(`text`, '[youtube]https://www.youtube.com/watch?v=', '[youtube]')");
    }
}
