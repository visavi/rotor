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
            [
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
            ],
            [
                'type'      => 'down_upload',
                'name'      => 'Уведомеление о загрузке файла',
                'text'      => 'Уведомеление о загрузке файла.
Новый файл [b][url=%url%]%title%[/url][/b] требует подтверждения на публикацию!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_publish',
                'name'      => 'Уведомеление о публикации файла',
                'text'      => 'Уведомеление о публикации файла.
Ваш файл [b][url=%url%]%title%[/url][/b] успешно прошел проверку и добавлен в загрузки',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_unpublish',
                'name'      => 'Уведомеление о снятии с публикации',
                'text'      => 'Уведомеление о снятии с публикации.
Ваш файл [b][url=%url%]%title%[/url][/b] снят с публикации из загрузок',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_change',
                'name'      => 'Уведомеление об изменении файла',
                'text'      => 'Уведомеление об изменении файла.
Ваш файл [b][url=%url%]%title%[/url][/b] был отредактирован модератором, возможно от вас потребуются дополнительные исправления!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'notify',
                'name'      => 'Упоминание пользователя',
                'text'      => 'Пользователь @%login% упомянул вас на странице [b][url=%url%]%title%[/url][/b]
Текст сообщения: %text%',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ]
        ];

        $table->insert($data)->save();
    }
}
