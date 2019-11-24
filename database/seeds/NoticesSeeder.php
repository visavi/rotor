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
Теперь Вы полноправный пользователь сайта, сохраните ваш логин и пароль в надежном месте, они пригодятся вам для входа на наш сайт.
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
                'name'      => 'Уведомление о загрузке файла',
                'text'      => 'Уведомеление о загрузке файла.
Новый файл [b][url=%url%]%title%[/url][/b] требует подтверждения на публикацию!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_publish',
                'name'      => 'Уведомление о публикации файла',
                'text'      => 'Уведомеление о публикации файла.
Ваш файл [b][url=%url%]%title%[/url][/b] успешно прошел проверку и добавлен в загрузки',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_unpublish',
                'name'      => 'Уведомление о снятии с публикации',
                'text'      => 'Уведомление о снятии с публикации.
Ваш файл [b][url=%url%]%title%[/url][/b] снят с публикации из загрузок',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_change',
                'name'      => 'Уведомление об изменении файла',
                'text'      => 'Уведомление об изменении файла.
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
            ],
            [
                'type'      => 'invite',
                'name'      => 'Отправка пригласительных ключей',
                'text'      => 'Поздравляем! Вы получили пригласительные ключи
Ваши ключи: %key%
С помощью этих ключей вы можете пригласить ваших друзей на наш сайт!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'contact',
                'name'      => 'Добавление в контакт-лист',
                'text'      => 'Пользователь @%login% добавил вас в свой контакт-лист!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'ignore',
                'name'      => 'Добавление в игнор-лист',
                'text'      => 'Пользователь @%login% добавил вас в свой игнор-лист!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'transfer',
                'name'      => 'Перевод денег',
                'text'      => 'Пользователь @%login% перечислил вам %money% 
Комментарий: %comment%',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'rating',
                'name'      => 'Перевод денег',
                'text'      => 'Пользователь @%login% поставил вам %vote%! (Ваш рейтинг: %rating%)
Комментарий: %comment%',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'surprise',
                'name'      => 'Новогодний сюрприз',
                'text'      => 'Поздравляем с новым %year% годом!
В качестве сюрприза вы получаете:
%point% 
%money%
%rating% репутации
Ура!!!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'explain',
                'name'      => 'Объяснение нарушения',
                'text'      => 'Объяснение нарушения: %message%',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ]
        ];

        $table->insert($data)->save();
    }
}
