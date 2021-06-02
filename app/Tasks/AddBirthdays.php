<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Models\Mailing;
use App\Models\User;
use Crontask\Tasks\Task;

class AddBirthdays extends Task
{
    /**
     * Добавляет именинников в рассылку
     */
    public function run()
    {
        $deliveryUsers = User::query()
            ->where('point', '>', 0)
            ->whereIn('level', User::USER_GROUPS)
            ->whereRaw('substr(birthday, 1, 5) = ?', date('d.m', SITETIME))
            ->get();

        if ($deliveryUsers->isNotEmpty()) {
            foreach ($deliveryUsers as $user) {
                $subject = 'С днем рождения от ' . setting('title');

                $text = 'Здравствуйте ' . e($user->getName()) . '!<br>Поздравляем Вас с Днём рождения и желаем счастья, здоровья, новых идей, творческого настроения и побольше радости и смеха!<br><br>Администрация сайта ' . setting('title');

                Mailing::query()->create([
                    'user_id'    => $user->id,
                    'type'       => 'birthdays',
                    'subject'    => $subject,
                    'text'       => $text,
                    'created_at' => SITETIME,
                ]);
            }
        }
    }

    /**
     * Minify html
     *
     * @param string $body
     *
     * @return string
     */
    private function minifyHtml(string $body): string
    {
        $search = [
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s',
        ];
        $replace = ['>', '<', ' '];

        return preg_replace($search, $replace, $body);
    }
}
