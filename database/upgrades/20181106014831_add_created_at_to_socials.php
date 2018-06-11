<?php

use Phinx\Migration\AbstractMigration;

class AddCreatedAtToSocials extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('socials');
        $table->addColumn('created_at', 'integer', ['null' => true])
            ->save();

        $this->execute('UPDATE socials SET created_at="' . SITETIME . '";');

        $table->changeColumn('created_at', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('socials');
        $table->removeColumn('created_at')
            ->save();
    }
}
