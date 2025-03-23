<?php

namespace App\Console\Commands;

use App\Models\Mailing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class MessageSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Message send';

    /**
     * Рассылает письма
     */
    public function handle(): int
    {
        $queues = Mailing::query()
            ->where('sent', 0)
            ->limit(setting('sendmailpacket'))
            ->get();

        if ($queues->isNotEmpty()) {
            foreach ($queues as $queue) {
                $user = getUserById($queue->user_id);

                if ($user) {
                    $data = [
                        'to'          => $user->email,
                        'subject'     => $queue->subject,
                        'text'        => $queue->text,
                        'unsubscribe' => $user->subscribe,
                    ];

                    sendMail('mailer.default', $data);
                }

                $queue->update([
                    'sent'    => 1,
                    'sent_at' => SITETIME,
                ]);
            }
        }

        $this->info('Message sent successfully.');

        return SymfonyCommand::SUCCESS;
    }
}
