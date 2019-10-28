<?php

use Phinx\Migration\AbstractMigration;

class AddDownsToNotices extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $rows = [
            [
                'type'      => 'down_upload',
                'name'      => 'Уведомление о загрузке файла',
                'text'      => 'Уведомление о загрузке файла.
Новый файл [b][url=%url%]%title%[/url][/b] требует подтверждения на публикацию!',
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ],
            [
                'type'      => 'down_publish',
                'name'      => 'Уведомление о публикации файла',
                'text'      => 'Уведомление о публикации файла.
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
            ]
        ];

        $this->table('notices')->insert($rows)->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("DELETE FROM notices WHERE type='down_upload';");
        $this->execute("DELETE FROM notices WHERE type='down_publish';");
        $this->execute("DELETE FROM notices WHERE type='down_unpublish';");
        $this->execute("DELETE FROM notices WHERE type='down_change';");
        $this->execute("DELETE FROM notices WHERE type='notify';");
    }
}
