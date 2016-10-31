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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 1;

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
downs event_text
events event_text
guest guest_text guest_reply
inbox inbox_text
news news_text
note note_text
notebook note_text
notice notice_text
offers offers_text outbox_text
outbox outbox_text
photo photo_text
posts posts_text
rating rating_text
spam spam_text
trash trash_text
users users_info users_reasonban
wall wall_text
*/


/*
[big][/big]  -> [size=4][/size]
[small][/small] -> [size=1][/size]
[red][/red]  ->  [color=#ff0000][/color]
[green][/green]  ->   [color=#00cc00][/color]
[blue][/blue]  ->  [color=#0000ff][/color]
[q][/q] -> [quote][/quote]
[del][/del] -> [s][/s]
*/

echo '<h1>Апгрейд тегов</h1>';

switch ($act):

case '1':
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[del]', '[s]');");
DB::run()->exec("UPDATE `banhist` SET `ban_reason`= REPLACE(`ban_reason`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '2':
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `blogs` SET `blogs_text`= REPLACE(`blogs_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '3':
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `chat` SET `chat_text`= REPLACE(`chat_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '4':
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `commblog` SET `commblog_text`= REPLACE(`commblog_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '5':
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `commevents` SET `commevent_text`= REPLACE(`commevent_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '6':
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `commload` SET `commload_text`= REPLACE(`commload_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '7':
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `commnews` SET `commnews_text`= REPLACE(`commnews_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '8':
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `commoffers` SET `comm_text`= REPLACE(`comm_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '9':
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `commphoto` SET `commphoto_text`= REPLACE(`commphoto_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '10':
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `downs` SET `downs_text`= REPLACE(`downs_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '11':
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `events` SET `event_text`= REPLACE(`event_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '12':
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `guest` SET `guest_text`= REPLACE(`guest_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '13':
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[del]', '[s]');");
DB::run()->exec("UPDATE `guest` SET `guest_reply`= REPLACE(`guest_reply`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '14':
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `inbox` SET `inbox_text`= REPLACE(`inbox_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '15':
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `news` SET `news_text`= REPLACE(`news_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '16':
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `note` SET `note_text`= REPLACE(`note_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '17':
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `notebook` SET `note_text`= REPLACE(`note_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '18':
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `notice` SET `notice_text`= REPLACE(`notice_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '19':
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `offers` SET `offers_text`= REPLACE(`offers_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '20':
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[del]', '[s]');");
DB::run()->exec("UPDATE `offers` SET `offers_text_reply`= REPLACE(`offers_text_reply`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '21':
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `outbox` SET `outbox_text`= REPLACE(`outbox_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;

case '22':
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `photo` SET `photo_text`= REPLACE(`photo_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '23':
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `posts` SET `posts_text`= REPLACE(`posts_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '24':
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `rating` SET `rating_text`= REPLACE(`rating_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '25':
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `spam` SET `spam_text`= REPLACE(`spam_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '26':
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `trash` SET `trash_text`= REPLACE(`trash_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '27':
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[del]', '[s]');");
DB::run()->exec("UPDATE `users` SET `users_info`= REPLACE(`users_info`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '28':
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[del]', '[s]');");
DB::run()->exec("UPDATE `users` SET `users_reasonban`= REPLACE(`users_reasonban`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


case '29':
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[big]', '[size=4]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/big]', '[/size]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[small]', '[size=1]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/small]', '[/size]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[red]', '[color=#ff0000]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/red]', '[/color]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[green]', '[color=#00cc00]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/green]', '[/color]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[blue]', '[color=#0000ff]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/blue]', '[/color]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[q]', '[quote]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/q]', '[/quote]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[del]', '[s]');");
DB::run()->exec("UPDATE `wall` SET `wall_text`= REPLACE(`wall_text`, '[/del]', '[/s]');");

echo '<a href="?act='.($act+1).'">Далее '.($act+1).'</a><br />';
break;


default:
	echo '<b>Апгрейд успешно выполнен!</b><br />';
endswitch;

include_once ('../themes/footer.php');
?>
