<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

set_time_limit(0);

/*
banhist  ban_reason
blogs blogs_text
chat chat_text
commblog commblog_text
commevents commevent_text
commload commload_text
commnews commnews_text
commoffers comm_text
commphoto commphoto_text
downs downs_text
events event_text
guest guest_text guest_reply
inbox inbox_text
news news_text
note note_text
notebook note_text
offers offers_text offers_text_reply
outbox outbox_text
photo photo_text
posts posts_text
rating rating_text
spam spam_text
trash trash_text
users users_info users_reasonban
wall wall_text
*/


DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '<br />', '\n');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '.gif\" alt=\"smile\" />', '');");


DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '<br />', '\n');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '<br />', '\n');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '<br />', '\n');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '<br />', '\n');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '.gif\" alt=\"smile\" />', '');");

DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '<br />', '\n');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '<img src=\"/images/smiles/', ':');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '.gif\" alt=\"smile\" />', '');");


echo '<b>Апгрейд успешно выполнен!</b><br />';

include_once ('../themes/footer.php');
?>
