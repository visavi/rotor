<?php

use Phinx\Migration\AbstractMigration;

class ChangeRelateTypeInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'string', array('limit' => 20))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('pollings');
        $table->changeColumn('relate_type', 'enum', ['values' => ['blog', 'down', 'offer', 'gallery']])
            ->save();
    }
}
