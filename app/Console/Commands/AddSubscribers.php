<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class AddSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'add:subscribers';

    /**
     * The console command description.
     */
    protected $description = 'Add subscribers';

    /**
     * Добавляет подписчиков в рассылку
     */
    public function handle(): int
    {
        $deliveryUsers = User::query()
            ->where('sendprivatmail', 0)
            ->whereIn('level', User::USER_GROUPS)
            ->whereHas('dialogues', static fn (Builder $query) => $query->where('reading', 0))
            ->where('updated_at', '<', now()->subDays((int) setting('sendprivatmailday')))
            ->whereNotNull('email')
            ->whereNotNull('subscribe')
            ->limit(100)
            ->get();

        $mail = app(MailService::class);
        $packet = max(1, (int) setting('sendmailpacket'));

        foreach ($deliveryUsers->values() as $index => $user) {
            // Подсчёт стоит запроса, в письме число нужно дважды
            $unread = $user->getCountNewMessages();

            $subject = $unread . ' непрочитанных сообщений на ' . setting('title');

            $text = 'Здравствуйте ' . e($user->getName()) . '!<br>У вас имеются непрочитанные сообщения (' . $unread . ' шт.) на сайте ' . setting('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . config('app.url') . '/messages">' . config('app.url') . '/messages</a><br><br><small>Если вы не хотите получать эти email, пожалуйста, <a href="' . config('app.url') . '/unsubscribe?key=' . $user->subscribe . '">откажитесь от подписки</a></small>';

            // Письма растаскиваются по минутам: суточный лимит релея
            // не должен выгорать одним залпом
            $mail->queue('mailer.default', [
                'to'          => $user->email,
                'subject'     => $subject,
                'text'        => $text,
                'unsubscribe' => $user->subscribe,
            ], intdiv((int) $index, $packet));

            $user->update(['sendprivatmail' => 1]);
        }

        $this->info('Subscribers successfully added.');

        return SymfonyCommand::SUCCESS;
    }
}
