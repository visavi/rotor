<?php

use App\Models\Blog;
use App\Models\Down;
use App\Models\Topic;
use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInReaders extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE readers SET relate_type="articles" WHERE relate_type="' . addslashes(Blog::class) . '";');
        $this->execute('UPDATE readers SET relate_type="downs" WHERE relate_type="' . addslashes(Down::class) . '";');
        $this->execute('UPDATE readers SET relate_type="topics" WHERE relate_type="' . addslashes(Topic::class) . '";');

        $table = $this->table('readers');
        $table->changeColumn('relate_type', 'string', ['limit' => 10])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('readers');
        $table->changeColumn('relate_type', 'string', ['limit' => 50])
            ->save();

        $this->execute('UPDATE readers SET relate_type="' . addslashes(Blog::class) . '" WHERE relate_type="articles";');
        $this->execute('UPDATE readers SET relate_type="' . addslashes(Down::class) . '" WHERE relate_type="downs";');
        $this->execute('UPDATE readers SET relate_type="' . addslashes(Topic::class) . '" WHERE relate_type="topics";');
    }
}
