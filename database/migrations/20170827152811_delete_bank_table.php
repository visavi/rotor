<?php

use Phinx\Migration\AbstractMigration;

class DeleteBankTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('bank');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('bank')) {
            $table = $this->table('bank', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user_id', 'integer')
                ->addColumn('sum', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('oper', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id', ['unique' => true])
                ->create();
        }
    }
}
