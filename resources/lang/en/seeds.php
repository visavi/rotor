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
        'register_text' => 'Welcome %username%!
Now you are a full user of the site, save your username and password in a safe place, they will come in handy for you to enter our site.
Before visiting the site, we recommend that you read the [url=/rules]site rules[/url], this will help you avoid unpleasant situations.
Have a nice time.
Regards, Site Administration!',

        'down_upload_name' => 'File download notification',
        'down_upload_text' => 'File download notification
The new file [b][url=%url%]%title%[/url][/b] requires confirmation of publication!',

        'down_publish_name' => 'File publishing notification',
        'down_publish_text' => 'File publishing notification.
Your file [b][url=%url%]%title%[/url][/b] successfully passed the test and was added to the downloads',

        'down_unpublish_name' => 'Notice of withdrawal from publication.',
        'down_unpublish_text' => 'Notice of withdrawal from publication.
Your file [b][url=%url%]%title%[/url][/b] has been unpublished',

        'down_change_name' => 'File change notification',
        'down_change_text' => 'File change notification.
Your file [b][url=%url%]%title%[/url][/b] has been edited by the moderator, you may need additional corrections!',

        'notify_name' => 'User mention',
        'notify_text' => 'User @%login% mentioned you on the page [b][url=%url%]%title%[/url][/b]
Message text: %text%',

        'invite_name' => 'Sending invitation keys',
        'invite_text' => 'Congratulations! You have received invitation keys
Your keys: %key%
With these keys you can invite your friends to our site!',

        'contact_name' => 'Add to contact list',
        'contact_text' => 'User @%login% added you to his contact list!',

        'ignore_name' => 'Adding to ignore list',
        'ignore_text' => 'User @%login% added you to his ignore list!',

        'transfer_name' => 'Money transfer',
        'transfer_text' => 'User @%login% transferred %money% to you
Comment: %comment%',

        'rating_name' => 'Reputation change',
        'rating_text' => 'User @%login% gave you %vote%! (Your rating: %rating%)
Comment: %comment%',

        'surprise_name' => 'New years surprise',
        'surprise_text' => 'Happy New %year% Year!
As a surprise, you get:
%point%
%money%
%rating% reputation
Cool!!!',

        'explain_name' => 'Explanation of violation',
        'explain_text' => 'Explanation of violation: %message%',

        'offer_reply_name' => 'Answer to problem / suggestion',
        'offer_reply_text' => <<<'INFO'
Notification of the answer to your problem / proposal
Your problem or suggestion [b][url=%url%]%title%[/url][/b] has been answered
Reply text: %text%
Entry status: %status%
INFO,

        'article_publish_name' => 'Notification of article publication',
        'article_publish_text' => 'Your article [b][url=%url%]%title%[/url][/b] has been published',

        'article_unpublish_name' => 'Notice of removal from publication',
        'article_unpublish_text' => 'Your article [b][url=%url%]%title%[/url][/b] has been removed from publication',
    ],
];
