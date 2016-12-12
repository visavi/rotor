<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeIndexByName('users_email');
        $table->removeIndexByName('users_login');
        $table->removeIndexByName('users_level');
        $table->removeIndexByName('users_nickname');
        $table->removeIndexByName('users_themes');
        $table->removeIndexByName('users_point');
        $table->removeIndexByName('users_money');
        $table->removeIndexByName('users_rating');
        $table->addIndex('email', ['unique' => true])
            ->addIndex('login', ['unique' => true])
            ->addIndex('level')
            ->addIndex('nickname')
            ->addIndex('themes')
            ->addIndex('point')
            ->addIndex('money')
            ->addIndex('rating')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->removeIndexByName('email');
        $table->removeIndexByName('login');
        $table->removeIndexByName('level');
        $table->removeIndexByName('nickname');
        $table->removeIndexByName('themes');
        $table->removeIndexByName('point');
        $table->removeIndexByName('money');
        $table->removeIndexByName('rating');
        $table->addIndex('email', ['unique' => true, 'name' => 'users_email'])
            ->addIndex('login', ['unique' => true, 'name' => 'users_login'])
            ->addIndex('level', ['name' => 'users_level'])
            ->addIndex('nickname', ['name' => 'users_nickname'])
            ->addIndex('themes', ['name' => 'users_themes'])
            ->addIndex('point', ['name' => 'users_point'])
            ->addIndex('money', ['name' => 'users_money'])
            ->addIndex('rating', ['name' => 'users_rating'])
            ->save();
    }
}
