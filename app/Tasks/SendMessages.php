<?php

namespace App\Tasks;

use App\Models\Mailing;
use Crontask\Tasks\Task;

class SendMessages extends Task
{
    /**
     * Рассылает письма
     */
    public function run()
    {
        $queues = Mailing::query()
            ->where('sent', 0)
            ->limit(setting('sendmailpacket'))
            ->get();

        if ($queues->isNotEmpty()) {
            foreach ($queues as $queue) {
                $user = getUserById($queue->user_id);

                sendMail($user->email, $queue->subject, $queue->text);
                $queue->update(['sent' => 1]);
            }
        }
    }
}
