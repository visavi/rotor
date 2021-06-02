<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Models\Mailing;
use Crontask\Tasks\Task;

class SendMessages extends Task
{
    /**
     * Рассылает письма
     */
    public function run(): void
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
                        'to'      => $user->email,
                        'subject' => $queue->subject,
                        'text'    => $queue->text,
                    ];

                    sendMail('mailer.default', $data);
                }

                $queue->update([
                    'sent'    => 1,
                    'sent_at' => SITETIME,
                ]);
            }
        }
    }
}
