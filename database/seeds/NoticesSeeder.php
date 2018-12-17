<?php

use Phinx\Seed\AbstractSeed;

class NoticesSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
    {
        $this->execute('TRUNCATE notices');

        $table = $this->table('notices');

        $data = [
            'type'      => 'register',
            'name'      => 'Приветствие при регистрации в приват',
            'text'      => 'Добро пожаловать, %username%!
Теперь Вы полноправный пользователь сайта, сохраните ваш пароль и логин в надежном месте, они пригодятся вам для входа на наш сайт.
Перед посещением сайта рекомендуем вам ознакомиться с [url=/rules]правилами сайта[/url], это поможет Вам избежать неприятных ситуаций.
Желаем приятно провести время.
С уважением, администрация сайта!',
            'user_id'    => 1,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
            'protect'    => 1,
        ];

        $table->insert($data)->save();
    }
}
