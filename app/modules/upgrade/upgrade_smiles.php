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

DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `ban_reason` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `ban_reason` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `ban_reason` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `blogs_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `blogs_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `blogs_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `chat_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `chat_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `chat_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `commblog_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `commblog_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `commblog_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `commevent_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `commevent_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `commevent_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `commload_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `commload_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `commload_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `commnews_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `commnews_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `commnews_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `comm_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `comm_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `comm_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `commphoto_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `commphoto_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `commphoto_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `downs_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `downs_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `downs_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `event_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `event_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `event_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `guest_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `guest_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `guest_text` LIKE '%<img src=\"../assets/img/images/%';");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `guest_reply` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `guest_reply` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `guest_reply` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `inbox_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `inbox_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `inbox_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `news_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `news_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `news_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `note_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `note_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `note_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `note_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `note_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `note_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `offers_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `offers_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `offers_text` LIKE '%<img src=\"../assets/img/images/%';");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `offers_text_reply` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `offers_text_reply` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `offers_text_reply` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `outbox_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `outbox_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `outbox_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `photo_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `photo_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `photo_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `posts_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `posts_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `posts_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `rating_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `rating_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `rating_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `spam_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `spam_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `spam_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `trash_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `trash_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `trash_text` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `users_info` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `users_info` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `users_info` LIKE '%<img src=\"../assets/img/images/%';");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `users_reasonban` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `users_reasonban` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `users_reasonban` LIKE '%<img src=\"../assets/img/images/%';");

DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '<img src=\"../images/smiles/', '<img src=\"/images/smiles/') WHERE `wall_text` LIKE '%<img src=\"../images/smiles/%';");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '<img src=\"../images/smiles2/', '<img src=\"/images/smiles/') WHERE `wall_text` LIKE '%<img src=\"../images/smiles2/%';");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '<img src=\"../assets/img/images/', '<img src=\"/assets/img/images/') WHERE `wall_text` LIKE '%<img src=\"../assets/img/images/%';");

echo '<b>Апгрейд успешно выполнен!</b><br />';

include_once ('../themes/footer.php');
?>
