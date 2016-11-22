<?php show_title('Документация RotorCMS'); ?>

<div class="b"><b>Стандартные функции</b></div>
- <a href="/files/docs/maketime">maketime</a><br />
- <a href="/files/docs/makestime">makestime</a><br />
- <a href="/files/docs/date_fixed">date_fixed</a><br />
- <a href="/files/docs/unlink_image">unlink_image</a><br />
- <a href="/files/docs/delete_users">delete_users</a><br />
- <a href="/files/docs/delete_album">delete_album</a><br />
- <a href="/files/docs/moneys">moneys</a><br />
- <a href="/files/docs/points">points</a><br />
- <a href="/files/docs/bb_code">bb_code</a><br />
- utf_to_win<br />
- win_to_utf<br />
- utf_lower<br />
- <a href="/files/docs/check">check</a><br />
- subtok<br />
- no_br<br />
- yes_br<br />
- smiles<br />
- nosmiles<br />
- formatsize<br />
- read_file<br />
- read_dir<br />
- formattime<br />
- antimat<br />
- user_status<br />
- save_title<br />
- user_title<br />
- save_nickname<br />
- nickname<br />
- save_setting<br />
- save_navigation<br />
- save_ipban<br />
- flood_period<br />
- is_flood<br />
- rating_vote<br />
- safe_encode<br />
- safe_decode<br />
- generate_password<br />
- text_dump<br />
- dump<br />
- scan_check<br />
- make_calendar<br />
- save_money<br />
- user_money<br />
- save_usermail<br />
- user_mail<br />
- save_avatar<br />
- user_avatars<br />
- cards_score<br />
- cards_points<br />
- user_contact<br />
- user_ignore<br />
- user_wall<br />
- stats_online<br />
- stats_counter<br />
- stats_users<br />
- stats_admins<br />
- stats_spam<br />
- stats_banned<br />
- stats_banhist<br />
- stats_reglist<br />
- stats_ipbanned<br />
- stats_gallery<br />
- stats_allnews<br />
- stats_blacklist<br />
- stats_headlines<br />
- stats_navigation<br />
- stats_antimat<br />
- stats_smiles<br />
- stats_avatars<br />
- stats_checker<br />
- stats_advert<br />
- <a href="/files/docs/user_position">user_position</a><br />
- <a href="/files/docs/chmode">chmode</a><br />
- <a href="/files/docs/user_online">user_online</a><br />
- allonline<br />
- user_visit<br />
- compress_output_gzip<br />
- compress_output_deflate<br />
- check_string<br />
- utf_substr<br />
- utf_strlen<br />
- is_utf<br />
- <a href="/files/docs/addmail">addmail</a><br />
- <a href="/files/docs/page_jumpnavigation">page_jumpnavigation</a><br />
- <a href="/files/docs/page_strnavigation">page_strnavigation</a><br />
- stats_blog<br />
- stats_forum<br />
- stats_guest<br />
- stats_chat<br />
- stats_newchat<br />
- stats_load<br />
- crypt_mail<br />
- intar<br />
- stats_votes<br />
- stats_news<br />
- last_news<br />
- verifi<br />
- quickcode<br />
- quicksmiles<br />
- quickpaste<br />
- check_user<br />
- <a href="/files/docs/is_user">is_user</a><br />
- is_admin<br />
- show_title<br />
- show_error<br />
- show_login<br />
- ob_processing<br />
- icons<br />
- shuffle_assoc<br />
- strip_str<br />
- show_advertuser<br />
- save_advertuser<br />
- stats_changes<br />
- curl_connect<br />
- recenttopics<br />
- recentfiles<br />
- recentblogs<br />
- stats_offers<br />
- fn_open<br />
- fn_write<br />
- fn_close<br />
- restatement<br />
- write_files<br />
- counter_string<br />
- strsearch<br />

- <a href="/files/docs/last_page">last_page</a><br />
- <a href="/files/docs/cache_functions">cache_functions</a><br />
- <a href="/files/docs/cache_admin_links">cache_admin_links</a><br />
- <a href="/files/docs/show_admin_links">show_admin_links</a><br />
- <a href="/files/docs/resize_image">resize_image</a><br />
- <a href="/files/docs/redirect">redirect</a><br />
- <a href="/files/docs/profile">profile</a><br />
- <a href="/files/docs/format_num">format_num</a><br />
- <a href="/files/docs/include_javascript">include_javascript</a><br />
- <a href="/files/docs/progress_bar">progress_bar</a><br />
<br />

<div class="b"><b>Стандартные классы</b></div>
<a href="/files/docs/class_dbm">DBM</a><br />
<a href="/files/docs/class_validation">Validation</a><br />
<a href="/files/docs/class_bbcodeparser">BBCodeParser</a><br />
<br />

<div class="b"><b>Константы</b></div>
<b>BASEDIR</b> - Определение корня сайта<br />
<b>DATADIR</b> - Расположение данных сайта, директория local (логи, бэкапы, кэш, запросы)<br />
<b>SITETIME</b> - Текущее время сайта time(), но с учетом часового пояса<br />
<br />

<div class="b"><b>Переменные</b></div>
<br />

<div class="b"><b>Таблицы базы данных</b></div>
 - <b>admlog</b> (Записи логов админки)<br />
 - <b>antimat</b> (Антимат)<br />
 - <b>avatars</b> (Аватары)<br />
 - <b>ban</b> (Хранение забаненых IP адресов)<br />
 - <b>banhist</b> (Банлист пользователей)<br />
 - <b>bank</b> (Хранение денег пользователей)<br />
 - <b>blacklist</b> (Чёрный список)<br />
 - <b>blogs</b> (Статьи блога)<br />
 - <b>bookmarks</b> (Закладки форума)<br />
 - <b>cats</b> (категории загруз центра (Названия категорий))<br />
 - <b>catsblog</b> (Категории блогов)<br />
 - <b>changemail</b> (Активация email (изменение e-mail у пользователей))<br />
 - <b>chat</b> (Админ чат)<br />
 - <b>comments</b> (Комментарии)<br />
 - <b>contact</b> (Контакт лист)<br />
 - <b>counter</b> (Счётчик посетителей)<br />
 - <b>counter24</b> (Счётчик за 24 часа)<br />
 - <b>counter31</b> (счетчик за месяц)<br />
 - <b>downs</b> (Описание файлов в загруз-центре)<br />
 - <b>error</b> (Таблица ошибок)<br />
 - <b>flood</b> (Данные антифлуда)<br />
 - <b>forums</b> (Список форумов)<br />
 - <b>ignore</b> (Игнор лист)<br />
 - <b>inbox</b> (Приват / входящие)<br />
 - <b>loads</b> (Загрузки)<br />
 - <b>login</b> (Авторизации пользователей)<br />
 - <b>navigation</b> (Навигация, быстрый переход, ссылки)<br />
 - <b>news</b> (Новости)<br />
 - <b>note</b> (Админские заметки о пользователях)<br />
 - <b>notebook</b> (Блокнот в личном кабинете)<br />
 - <b>offers</b> (Предложения и проблемы)<br />
 - <b>online</b> (Вывод онлайн, кто на сайте)<br />
 - <b>outbox</b> (Приват / исходящие)<br />
 - <b>posts</b> (Посты форума)<br />
 - <b>poolings</b> (Логи рейтинга)<br />
 - <b>rating</b> (Рейтинг, изменение авторитета, плюсы и минусы)<br />
 - <b>readblog</b> (Прочтений в блогах)<br />
 - <b>rekuser</b> (реклама пользователей)<br />
 - <b>rules</b> (правила)<br />
 - <b>setting</b> (Настройки сайта)<br />
 - <b>smiles</b> (Смайлы)<br />
 - <b>spam</b> (Спам лист)<br />
 - <b>status</b> (Статус юзера)<br />
 - <b>topics</b> (Топики форума)<br />
 - <b>transfers</b> (суммы перевода денег)<br />
 - <b>trash</b> (Приват / Корзина)<br />
 - <b>users</b> (Анкета пользователя)<br />
 - <b>visit</b> (Дата посещений)<br />
 - <b>vote</b> (Вопросы в голосованиях)<br />
 - <b>voteanswer</b> (Ответы на голосования)<br />
 - <b>votepoll</b> (кто голосовал)<br />
 - <b>wall</b> (Записи на стене)<br />

<br />
Напишите о функции которой нет в списке или у которой нет описания, сделайте примеры отправьте на <a href="mailto:<?=crypt_mail('visavi.net@mail.ru')?>?subject=<?=rawurlencode('Описание функции')?>"><?=crypt_mail('visavi.net@mail.ru')?></a> и получите до 5 тысяч чатлов за одно полное описание и до 10 тыс за описание небольшой функции<br />

<br /><b><a href="/assets/example.txt">Скачать пример файла документации</a></b><br />
<br />
