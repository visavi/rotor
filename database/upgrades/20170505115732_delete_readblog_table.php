<?php

use Phinx\Migration\AbstractMigration;

class DeleteReadblogTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('readblog');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('readblog')) {
            $table = $this->table('readblog', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('blog', 'integer', ['signed' => false])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
