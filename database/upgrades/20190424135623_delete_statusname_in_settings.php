<?php

use Phinx\Migration\AbstractMigration;

class DeleteStatusnameInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM settings WHERE name='statusname';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('statusname', 'Владелец,Админ,Модератор,Редактор,Пользователь,Ожидающий,Забаненный');");
    }
}
