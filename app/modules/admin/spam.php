<?php
App::view(App::setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$type = check(Request::input('type'));
$page = abs(intval(Request::input('page', 1)));


$types = [
    'post'  => Post::class,
    'guest' => Guest::class,
];

$type = isset($types[$type]) ? $type : 'post';

if (is_admin([101, 102, 103])) {
    //show_title('Список жалоб');

    $total = Spam::select(Capsule::raw("
        SUM(relate_type='".Guest::class."') guest,
        SUM(relate_type='".Post::class."') post
    "))->first();

    switch ($act):
    ############################################################################################
    ##                                         Форум                                          ##
    ############################################################################################
        case 'index':
            echo '<a href="/admin/spam?type=post">Форум</a> ('.$total['post'].') / <a href="/admin/spam?type=guest">Гостевая</a> ('.$total['guest'].') / <a href="/admin/spam?type=privat">Приват</a> ('.$total['inbox'].') / <a href="/admin/spam?type=wall">Стена</a> ('.$total['wall'].') / <a href="/admin/spam?type=load">Загрузки</a> ('.$total['load'].') / <a href="/admin/spam?type=blog">Блоги</a> ('.$total['blog'].')<br /><br />';

            $page = App::paginate(App::setting('spamlist'), $total['post']);
            if ($page['total'] > 0) {

                $records = Spam::where('relate_type', $types[$type])
                    ->orderBy('created_at', 'desc')
                    ->offset($page['offset'])
                    ->limit(App::setting('spamlist'))
                    ->with('relate.user', 'user')
                    ->get();

                echo '<form action="/admin/spam?act=del&amp;type='.$type.'&amp;page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'" />';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                foreach($records as $data) {

                    if ($data->relate){
                        echo '<div class="b">';
                        echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
                        echo '<i class="fa fa-file-o"></i> <b>'.profile($data->relate->user).'</b> <small>('.date_fixed($data->relate->created_at, "d.m.y / H:i:s").')</small></div>';
                        echo '<div>'.App::bbCode($data->relate->text).'</div>';
                    } else {
                        echo '<div class="b">';
                        echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
                        echo '<i class="fa fa-file-o"></i> <b>Сообщение не найдено</b>';
                        echo '</div>';
                    }

                    echo '<div><a href="'.$data['link'].'">Перейти к сообщению</a><br />';
                    echo 'Жалоба: '.profile($data->user).' ('.date_fixed($data['created_at']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                App::pagination($page);

                if (is_admin([101, 102])) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;token='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                 Удаление сообщений                                     ##
        ############################################################################################
        case "del":

            $token = check(Request::input('token'));
            $del = intar(Request::input('del'));

            if ($token == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM `spam` WHERE `id` IN (".$del.");");

                    notice('Выбранные жалобы успешно удалены!');
                    redirect("/admin/spam?type=$type&page=$page");

                } else {
                    show_error('Ошибка! Отсутствуют выбранные жалобы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/spam?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                     Очистка жалоб                                      ##
        ############################################################################################
        case 'clear':

            $token = check(Request::input('token'));

            if ($token == $_SESSION['token']) {
                if (is_admin([101, 102])) {
                    DB::run() -> query("TRUNCATE `spam`;");

                    notice('Жалобы успешно очищены!');
                    redirect("/admin/spam");

                } else {
                    show_error('Ошибка! Очищать жалобы могут только админы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/spam">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect("/");
}

App::view(App::setting('themes').'/foot');
