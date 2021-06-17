<?php

namespace App\Console\Commands;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Console\Command;

class AddSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:subscribers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add subscribers';

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
     * Добавляет подписчиков в рассылку
     *
     * @return int
     */
    public function handle()
    {
        $deliveryUsers = User::query()
            ->where('sendprivatmail', 0)
            ->whereIn('level', User::USER_GROUPS)
            ->where('newprivat', '>', 0)
            ->where('updated_at', '<', strtotime('-' . setting('sendprivatmailday') . ' days', SITETIME))
            ->whereNotNull('subscribe')
            ->limit(100)
            ->get();

        if ($deliveryUsers->isNotEmpty()) {
            foreach ($deliveryUsers as $user) {
                $subject = $user->newprivat . ' непрочитанных сообщений на ' . setting('title');

                $text = 'Здравствуйте ' . e($user->getName()) . '!<br>У вас имеются непрочитанные сообщения (' . $user->newprivat . ' шт.) на сайте ' . setting('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . config('app.url') . '/messages">' . config('app.url') . '/messages</a><br><br><small>Если вы не хотите получать эти email, пожалуйста, <a href="'.config('app.url').'/unsubscribe?key='.$user->subscribe.'">откажитесь от подписки</a></small>';

                Mailing::query()->create([
                    'user_id'    => $user->id,
                    'type'       => 'messages',
                    'subject'    => $subject,
                    'text'       => $text,
                    'created_at' => SITETIME,
                ]);

                $user->update(['sendprivatmail' => 1]);
            }
        }

        $this->info('Subscribers successfully added.');

        return 0;
    }
}
