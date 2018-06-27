<?php

use Phinx\Migration\AbstractMigration;

class ChangeImageInNews extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('news');
        $table->changeColumn('image', 'string', ['limit' => 100, 'null' => true])
            ->save();

        $this->execute('UPDATE news SET image=concat("/uploads/news/", image) WHERE image IS NOT NULL;');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('news');
        $table->changeColumn('image', 'string', ['limit' => 30, 'null' => true])
            ->save();

        $this->execute('UPDATE news SET image=replace(image, "/uploads/news/", "") WHERE image IS NOT NULL;');
    }
}
