<?php

namespace App\Console\Commands;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Console\Command;

class AddBirthdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:birthdays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add birthdays';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Добавляет именинников в рассылку
     *
     * @return int
     */
    public function handle()
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

        $this->info('Birthdays successfully added.');

        return 0;
    }
}
