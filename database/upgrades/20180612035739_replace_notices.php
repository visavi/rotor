<?php

use Phinx\Migration\AbstractMigration;

class ReplaceNotices extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE notices SET text = 'Добро пожаловать, %username%!
Теперь Вы полноправный пользователь сайта, сохраните ваш пароль и логин в надежном месте, они пригодятся вам для входа на наш сайт.
Перед посещением сайта рекомендуем вам ознакомиться с [url=/rules]правилами сайта[/url], это поможет Вам избежать неприятных ситуаций.
Желаем приятно провести время.
С уважением, администрация сайта!' where type='register'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
