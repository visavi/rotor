<?php

use Phinx\Migration\AbstractMigration;

class CreateCommentsTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('comments', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('relate_type', 'enum', ['values' => ['blog', 'down', 'news', 'offer', 'gallery']])
            ->addColumn('relate_category_id', 'integer', ['signed' => false])
            ->addColumn('relate_id', 'integer', ['signed' => false])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer', ['signed' => false])
            ->addIndex(['relate_type', 'relate_id'])
            ->addIndex('time')
            ->create();
    }
}
