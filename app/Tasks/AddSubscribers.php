<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Models\Mailing;
use App\Models\User;
use Crontask\Tasks\Task;

class AddSubscribers extends Task
{
    /**
     * Добавляет подписчиков в рассылку
     */
    public function run()
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

                $message = 'Здравствуйте ' . $user->getName() . '!<br>У вас имеются непрочитанные сообщения (' . $user->newprivat . ' шт.) на сайте ' . setting('title') . '<br>Прочитать свои сообщения вы можете по адресу <a href="' . siteUrl(true) . '/messages">' . siteUrl(true) . '/messages</a><br><br><small>Если вы не хотите получать эти email, пожалуйста, <a href="'.siteUrl(true).'/unsubscribe?key='.$user->subscribe.'">откажитесь от подписки</a></small>';

                $body = view('mailer.default', compact('subject', 'message'));
                $body = $this->minifyHtml($body);

                Mailing::query()->create([
                    'user_id'    => $user->id,
                    'type'       => 'messages',
                    'subject'    => $subject,
                    'text'       => $body,
                    'created_at' => SITETIME,
                ]);

                $user->update(['sendprivatmail' => 1]);
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
