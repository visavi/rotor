<?php

use App\Models\Queue;
use App\Models\User;

require __DIR__.'/bootstrap.php';

// Добавляем подписчиков
$deliveryUsers = User::query()
    ->where('sendprivatmail', 0)
    ->whereIn('level', User::USER_GROUPS)
    ->where('newprivat', '>', 0)
    ->where('timelastlogin', '<', SITETIME - 86400 * setting('sendprivatmailday'))
    ->whereNotNull('subscribe')
    ->groupBy('users.id')
    ->limit(setting('sendmailpacket'))
    ->get();


if ($deliveryUsers->isNotEmpty()) {
    foreach ($deliveryUsers as $user) {

        $subject = $user->newprivat . ' непрочитанных сообщений (' . setting('title') . ')';

        $message = 'Здравствуйте ' . $user->login . '!<br>У вас имеются непрочитанные сообщения (' . $user->newprivat . ' шт.) на сайте ' . setting('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . siteLink(setting('home')) . '/private">' . siteLink(setting('home')) . '/private</a><br><br><small>Если вы не хотите получать эти email, пожалуйста, <a href="'.siteLink(setting('home')).'/unsubscribe?key='.$user->subscribe.'">откажитесь от подписки</a></small>';

        $body = view('mailer.default', compact('subject', 'message'), true);

        Queue::query()->create([
            'user_id'    => $user->id,
            'type'       => 'private',
            'subject'    => $subject,
            'text'       => $body,
            'created_at' => SITETIME,
        ]);

        $user->update(['sendprivatmail' => 1]);
    }
 }

// Рассылка писем
$queues = Queue::query()
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
