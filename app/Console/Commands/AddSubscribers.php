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

            // Письма растаскиваются по минутам: суточный лимит релея
            // не должен выгорать одним залпом
            $mail->queue('mailer.unread', [
                'to'          => $user->email,
                'subject'     => __('mailer.unread_subject', ['site' => setting('title'), 'count' => $unread]),
                'username'    => $user->getName(),
                'count'       => $unread,
                'unsubscribe' => $user->subscribe,
            ], intdiv((int) $index, $packet));

            $user->update(['sendprivatmail' => 1]);
        }

        $this->info('Subscribers successfully added.');

        return SymfonyCommand::SUCCESS;
    }
}
