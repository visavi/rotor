<?php

use Phinx\Migration\AbstractMigration;

class AddOtherToNotices extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $rows = [
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
                'name'      => 'Изменение репутации',
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

        $this->table('notices')->insert($rows)->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("DELETE FROM notices WHERE type='invite';");
        $this->execute("DELETE FROM notices WHERE type='contact';");
        $this->execute("DELETE FROM notices WHERE type='ignore';");
        $this->execute("DELETE FROM notices WHERE type='transfer';");
        $this->execute("DELETE FROM notices WHERE type='rating';");
        $this->execute("DELETE FROM notices WHERE type='surprise';");
    }
}
