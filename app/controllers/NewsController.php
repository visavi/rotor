<?php

class NewsController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = News::count();
        $page = App::paginate(Setting::get('postnews'), $total);

        $news = News::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        App::view('news/index', compact('news', 'page'));
    }

    /**
     * Вывод новости
     */
    public function view($id)
    {
        $news = News::find($id);

        if (! $news) {
            App::abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $news['text'] = str_replace('[cut]', '', $news['text']);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $comments = $comments->reverse();

        App::view('news/view', compact('news', 'comments'));
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $datanews = News::find($id);

        if (!empty($datanews)) {

            $total = Comment::where('relate_type', News::class)
                ->where('relate_id', $id)
                ->count();

            $page = App::paginate(Setting::get('postnews'), $total);

            //Setting::get('newtitle') = 'Комментарии - '.$datanews['title'];
            //Setting::get('description') =  'Комментарии - '.$datanews['title'].' (Стр. '.$page['current'].')';

            echo '<h1><a href="/news/' . $datanews['id'] . '">' . $datanews['title'] . '</a></h1>';

            if ($total > 0) {

                $is_admin = is_admin();
                if ($is_admin) {
                    echo '<form action="/news/' . $id . '/delete?page=' . $page['current'] . '" method="post">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                }

                $comments = Comment::where('relate_type', News::class)
                    ->where('relate_id', $id)
                    ->offset($page['offset'])
                    ->limit($page['limit'])
                    ->orderBy('created_at')
                    ->with('user')
                    ->get();

                foreach ($comments as $data) {

                    echo '<div class="b" id="comment_' . $data['id'] . '"">';
                    echo '<div class="img">' . user_avatars($data['user']) . '</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="' . $data['id'] . '" /></span>';
                    }

                    echo '<b>' . profile($data['user']) . '</b>';
                    echo '<small> (' . date_fixed($data['created_at']) . ')</small><br />';
                    echo user_title($data['user']) . ' ' . user_online($data['user']) . '</div>';

                    echo '<div>' . App::bbCode($data['text']) . '<br />';

                    if (is_admin()) {
                        echo '<span class="data">(' . $data['brow'] . ', ' . $data['ip'] . ')</span>';
                    }

                    echo '</div>';
                }

                if ($is_admin) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                App::pagination($page);
            }

            if (empty($datanews['closed'])) {

                if (!$total) {
                    show_error('Комментариев еще нет!');
                }

                if (is_user()) {
                    echo '<div class="form"><form action="/news/' . $id . '/create" method="post">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                    echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Написать" /></form></div>';

                    echo '<br />';
                    echo '<a href="/rules">Правила</a> / ';
                    echo '<a href="/smiles">Смайлы</a> / ';
                    echo '<a href="/tags">Теги</a><br /><br />';
                } else {
                    show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
                }
            } else {
                show_error('Комментирование данной новости закрыто!');
            }
        } else {
            show_error('Ошибка! Выбранная новость не существует, возможно она была удалена!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
    }

    /**
     * Добавление комментариев
     */
    public function create($id)
    {

        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));
        $page = abs(intval(Request::input('page', 1)));

        if (is_user()) {

            $data = DB::run()->queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('equal', [Flood::isFlood(App::getUserId()), true], 'Антифлуд! Разрешается комментировать раз в ' . Flood::getPeriod() . ' сек!')
                ->addRule('not_empty', $data, 'Выбранной новости не существует, возможно она было удалена!')
                ->addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
                ->addRule('empty', $data['closed'], 'Комментирование данной новости запрещено!');

            if ($validation->run()) {

                $msg = antimat($msg);

                DB::run()->query("INSERT INTO `comments` (relate_type, `relate_id`, `text`, `user_id`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", ['news', $id, $msg, App::getUserId(), SITETIME, App::getClientIp(), App::getUserAgent()]);

                DB::run()->query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `created_at` DESC LIMIT " . Setting::get('maxkommnews') . ") AS del);", ['news', $id, 'news', $id]);

                DB::run()->query("UPDATE `news` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
                DB::run()->query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

                App::setFlash('success', 'Комментарий успешно добавлен!');

                if (isset($_GET['read'])) {
                    App::redirect('/news/' . $id);
                }

                App::redirect('/news/' . $id . '/end');

            } else {
                show_error($validation->getErrors());
            }
        } else {
            show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/' . $id . '/comments?page=' . $page . '">Вернуться</a><br />';
        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
    }

    /**
     * Удаление комментариев
     */
    public function delete()
    {
        $token = check(Request::input('token'));
        $del = intar(Request::input('del'));
        $page = abs(intval(Request::input('page', 1)));

        if (is_admin()) {
            if ($token == $_SESSION['token']) {
                if (!empty($del)) {

                    $del = implode(',', $del);

                    $delcomments = DB::run()->exec("DELETE FROM `comments` WHERE relate_type='news' AND `relate_id`=" . $id . " AND `id` IN (" . $del . ");");

                    DB::run()->query("UPDATE `news` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

                    App::setFlash('success', 'Выбранные комментарии успешно удалены!');
                    App::redirect('/news/' . $id . '/comments?page=' . $page);

                } else {
                    show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/' . $id . '/comments?page=' . $page . '">Вернуться</a><br />';
        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br />';
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {

        $news = News::find($id);

        if (empty($news)) {
            App::abort(404, 'Ошибка! Данной новости не существует!');
        }

        $end = ceil($news['comments'] / Setting::get('postnews'));
        App::redirect('/news/' . $id . '/comments?page=' . $end);
    }

    /**
     * Rss новостей
     */
    public function rss()
    {
        ?>
        <?= '<?xml version="1.0" encoding="utf-8"?>' ?>
        <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
            <channel>
                <title>Новости - <?=Setting::get('title')?></title>
                <link><?=Setting::get('home')?></link>
                <description>RSS новостей - <?=Setting::get('title')?></description>
                <image>
                    <url><?=Setting::get('logotip')?></url>
                    <title>Новости - <?=Setting::get('title')?></title>
                    <link><?=Setting::get('home')?></link>
                </image>
                <language>ru</language>
                <copyright><?=Setting::get('copy')?></copyright>
                <managingEditor><?=Setting::get('emails')?> (<?=Setting::get('nickname')?>)</managingEditor>
                <webMaster><?=Setting::get('emails')?> (<?=Setting::get('nickname')?>)</webMaster>
                <lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

                <?php $newses = News::orderBy('created_at', 'desc')->limit(15)->get(); ?>
                <?php foreach($newses as $news): ?>
                    <?php $news['text'] = App::bbCode($news['text']); ?>
                    <?php $news['text'] = str_replace(['/uploads/smiles', '[cut]'], [Setting::get('home').'/uploads/smiles', ''], $news['text']); ?>
                    <?php $news['text'] = htmlspecialchars($news['text']); ?>

                    <item>
                        <title><?=$news['title']?></title>
                        <link><?=Setting::get('home')?>/news/<?=$news['id']?></link>
                        <description><?=$news['text']?> </description>
                        <author><?=$news->getUser()->login?></author>
                        <pubDate><?=date("r", $news['created_at'])?></pubDate>
                        <category>Новости</category>
                        <guid><?=Setting::get('home')?>/news/<?=$news['id']?></guid>
                    </item>
                <?php endforeach; ?>

            </channel>
        </rss>
        <?php
    }
}
