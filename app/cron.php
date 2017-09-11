<?php

use App\Models\Queue;
use App\Models\User;

require __DIR__.'/bootstrap.php';

/*$deliveryUsers = User::select('users.*')
    ->where('sendprivatmail', 0)
    ->leftJoin('queue', function($join){
        $join->on('users.id', '=', 'queue.user_id')
            ->where('queue.type', 'private')
            ->where('queue.sent', 0);
    })
    ->where('confirmreg', 0)
    ->where('newprivat', '>', 0)
    ->where('timelastlogin', '<', SITETIME - 86400 * setting('sendprivatmailday'))
    ->whereNotNull('subscribe')
    ->groupBy('users.id')
    ->get();

if ($deliveryUsers->isNotEmpty()) {
    foreach ($deliveryUsers as $user) {

        $subject = $user['newprivat'] . ' непрочитанных сообщений (' . setting('title') . ')';
        $message = 'Здравствуйте ' . $user['login'] . '!<br>У вас имеются непрочитанные сообщения (' . $user['newprivat'] . ' шт.) на сайте ' . setting('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . siteLink(setting('home')) . '/private">' . siteLink(setting('home')) . '/private</a>';
        $body = view('mailer.default', compact('subject', 'message'), true);

        $xxx = Queue::create([
            'user_id'    => $user->id,
            'type'       => 'private',
            'subject'    => $subject,
            'text'       => $body,
            'created_at' => SITETIME,
        ]);

        var_dump(333);*/

        //sendMail($user['email'], $subject, $body, ['subscribe' => $user['subscribe']]);
        //$user->update(['sendprivatmail' => 1]);
    //}
// }
return true;
