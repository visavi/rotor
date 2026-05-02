<?php

return [
    'rules' => 'General rules for users of the site %SITENAME%',

    'settings' => [
        'language'       => 'en',
        'currency'       => 'usd',
        'guest_user'     => 'Guest',
        'deleted_user'   => 'Deleted Account',
        'default_status' => 'Spirit',
        'description'    => 'Short description of your site',
        'logos'          => 'Rotor Website',
        'moneyname'      => 'coin, coins, coins',
        'scorename'      => 'point, point, points',
    ],

    'statuses' => [
        'novice'       => 'Novice',
        'local'        => 'Local',
        'advanced'     => 'Advanced',
        'experienced'  => 'Experienced',
        'specialist'   => 'Specialist',
        'expert'       => 'Expert',
        'master'       => 'Master',
        'professional' => 'Professional',
        'guru'         => 'Guru',
        'legend'       => 'Legend',
    ],

    'notices' => [
        'register_name' => 'Greetings at registration in private',
        'register_text' => '<p>Welcome %username%!</p><p>Now you are a full user of the site, save your username and password in a safe place, they will come in handy for you to enter our site.</p><p>Before visiting the site, we recommend that you read the <a href="/rules">site rules</a>, this will help you avoid unpleasant situations.</p><p>Have a nice time.</p><p>Regards, Site Administration!</p>',

        'down_upload_name' => 'File download notification',
        'down_upload_text' => '<p>File download notification.</p><p>The new file <strong>%page%</strong> requires confirmation of publication!</p>',

        'down_publish_name' => 'File publishing notification',
        'down_publish_text' => '<p>File publishing notification.</p><p>Your file <strong>%page%</strong> successfully passed the test and was added to the downloads</p>',

        'down_unpublish_name' => 'Notice of withdrawal from publication',
        'down_unpublish_text' => '<p>Notice of withdrawal from publication.</p><p>Your file <strong>%page%</strong> has been unpublished</p>',

        'down_change_name' => 'File change notification',
        'down_change_text' => '<p>File change notification.</p><p>Your file <strong>%page%</strong> has been edited by the moderator, you may need additional corrections!</p>',

        'notify_name' => 'User mention',
        'notify_text' => '<p>User %login% mentioned you on the page <strong>%page%</strong></p><p>%text%</p>',

        'comment_reply_name' => 'Reply to comment',
        'comment_reply_text' => '<p>User %login% replied to your comment on the page <strong>%page%</strong></p><p>%text%</p>',

        'invite_name' => 'Sending invitation keys',
        'invite_text' => '<p>Congratulations! You have received invitation keys</p><p>Your keys: %key%</p><p>With these keys you can invite your friends to our site!</p>',

        'contact_name' => 'Add to contact list',
        'contact_text' => '<p>User %login% added you to his contact list!</p>',

        'ignore_name' => 'Adding to ignore list',
        'ignore_text' => '<p>User %login% added you to his ignore list!</p>',

        'transfer_name' => 'Money transfer',
        'transfer_text' => '<p>User %login% transferred %money% to you</p><p>Comment: %comment%</p>',

        'rating_name' => 'Reputation change',
        'rating_text' => '<p>User %login% gave you %vote%! (Your rating: %rating%)</p><p>Comment: %comment%</p>',

        'surprise_name' => 'New years surprise',
        'surprise_text' => '<p>Happy New %year% Year!</p><p>As a surprise, you get:</p><p>%point%</p><p>%money%</p><p>%rating% reputation</p><p>Cool!!!</p>',

        'explain_name' => 'Explanation of violation',
        'explain_text' => '<p>Explanation of violation: %message%</p>',

        'offer_reply_name' => 'Answer to problem / suggestion',
        'offer_reply_text' => '<p>Notification of the answer to your problem / proposal</p><p>Your problem or suggestion <strong>%page%</strong> has been answered</p><p>Reply text: %text%</p><p>Entry status: %status%</p>',

        'article_publish_name' => 'Notification of article publication',
        'article_publish_text' => '<p>Your article <strong>%page%</strong> has been published</p>',

        'article_unpublish_name' => 'Notice of removal from publication',
        'article_unpublish_text' => '<p>Your article <strong>%page%</strong> has been removed from publication</p>',
    ],
];
