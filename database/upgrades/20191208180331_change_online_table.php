<?php

use Phinx\Migration\AbstractMigration;

class ChangeOnlineTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('online')->drop()->save();

        $table = $this->table('online', [
            'engine'    => config('DB_ENGINE'),
            'collation' => config('DB_COLLATION')
        ]);

        $table
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('updated_at', 'integer', ['null' => true])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('online')->drop()->save();

        $table = $this->table('online', [
            'engine'    => config('DB_ENGINE'),
            'collation' => config('DB_COLLATION')
        ]);

        $table
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('updated_at', 'integer', ['null' => true])
            ->create();
    }
}
