<?php

use App\Classes\Hook;

/*
Список хуков

sidebarTreeviewStart
sidebarTreeviewEnd
sidebarTreeviewGuestStart
sidebarTreeviewGuestEnd
sidebarMenuStart
sidebarMenuEnd
sidebarFooterStart
sidebarFooterEnd

head
header
footer

contentStart
contentEnd

navbarStart
navbarEnd
navbarMenuStart
navbarMenuEnd

footerStart
footerEnd

userStart - анкетка сверху
userEnd - анкета снизу

userActionStart - анкета нижний блок сверху

userPersonalStart - своя анкета сверху
userPersonalEnd - своя анкета снизу

userNotPersonalStart($user) - чужая анкета сверху
userNotPersonalEnd($user) - чужая анкета снизу

userActionEnd - блок снизу
 */

// Пример хука
/* Hook::add('head', function ($content) {
    return $content . '<link rel="stylesheet" href="style.css">' . PHP_EOL;
}); */
