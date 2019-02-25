
<?php

use Phinx\Migration\AbstractMigration;

class CreateSocialsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('socials')) {
            $table = $this->table('socials', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('network', 'string')
                ->addColumn('uid', 'string')
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->create();
        }
    }
}
