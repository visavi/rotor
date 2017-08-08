<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$cid = (isset($_GET['cid'])) ? abs(intval($_GET['cid'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$sort = (isset($_GET['sort'])) ? check($_GET['sort']) : 'date';
$page = abs(intval(Request::input('page', 1)));

//show_title('Загрузки');

switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    if (!empty($cid)) {
        $cats = Category::raw_query("SELECT c.*, c2.id subcats_id, c2.name subcats_name FROM cats c 
          LEFT JOIN cats c2 ON c.parent = c2.id 
          WHERE c.id=:id LIMIT 1;",
            ['id' => $cid]
        )->find_one();

        if (!empty($cats)) {?>

            <?php //show_title($cats['name']); ?>

            <?php
            if (is_admin([101, 102])) {
                echo '<a href="/admin/load?act=down&amp;cid='.$cid.'&amp;page='.$page.'">Управление</a>';
            }
            ?>

            <ol class="breadcrumb">
                <li><a href="/load">Категории</a></li>

            <?php if ($cats['subcats_id']): ?>
                <li><a href="/load/down?cid=<?= $cats['subcats_id'] ?>"><?= $cats['subcats_name'] ?></a></ li>
            <?php endif; ?>

                <li class="active"><?= $cats['name'] ?></li>
            </ol>

            <?php if (is_user() && ! $cats['closed']): ?>
                <div class="pull-right">
                    <a class="btn btn-success" href="/load/add?cid=<?= $cid ?>">Добавить файл</a>
                </div><br />
            <?php endif; ?>

            <?php
            switch ($sort) {
                case 'rated': $order = 'rated';
                    break;
                case 'comm': $order = 'comments';
                    break;
                case 'loads': $order = 'loads';
                    break;
                default: $order = 'time';
            }

            echo '<br />Сортировать: ';

            if ($order == 'time') {
                echo '<b>По дате</b> / ';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=date">По дате</a> / ';
            }

            if ($order == 'loads') {
                echo '<b>Скачивания</b> / ';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=loads">Скачивания</a> / ';
            }

            if ($order == 'rated') {
                echo '<b>Оценки</b> / ';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=rated">Оценки</a> / ';
            }

            if ($order == 'comments') {
                echo '<b>Комментарии</b>';
            } else {
                echo '<a href="/load/down?cid='.$cid.'&amp;sort=comm">Комментарии</a>';
            }

            $querysub = DB::run() -> query("SELECT * FROM `cats` WHERE `parent`=?;", [$cid]);
            $sub = $querysub -> fetchAll();

            if (count($sub) > 0 && $page == 1) {
                foreach($sub as $subdata) {
                    echo '<div class="b"><i class="fa fa-folder-open"></i> ';
                    echo '<b><a href="/load/down?cid='.$subdata['id'].'">'.$subdata['name'].'</a></b> ('.$subdata['count'].')</div>';
                }
                echo '<hr />';
            }

            $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `category_id`=? AND `active`=?;", [$cid, 1]);
            $page = App::paginate(Setting::get('downlist'), $total);

            if ($total > 0) {


                $querydown = DB::run() -> query("SELECT * FROM `downs` WHERE `category_id`=? AND `active`=? ORDER BY ".$order." DESC LIMIT ".$page['offset'].", ".Setting::get('downlist').";", [$cid, 1]);

                $folder = $cats['folder'] ? $cats['folder'].'/' : '';

                while ($data = $querydown -> fetch()) {

                    $filesize = (!empty($data['link'])) ? read_file(HOME.'/uploads/files/'.$folder.$data['link']) : 0;

                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> ';
                    echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';
                    echo '<div>';

                    echo 'Скачиваний: '.$data['loads'].'<br />';

                    $rating = (!empty($data['rated'])) ? round($data['rating'] / $data['rated'], 1) : 0;

                    echo 'Рейтинг: <b>'.$rating.'</b> (Голосов: '.$data['rated'].')<br />';
                    echo '<a href="/load/down?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
                    echo '<a href="/load/down?act=end&amp;id='.$data['id'].'">&raquo;</a></div>';
                }

                App::pagination($page);
            } else {
                if (empty($cats['closed'])) {
                    show_error('В данном разделе еще нет файлов!');
                }
            }

            if (!empty($cats['closed'])) {
                show_error('В данном разделе запрещена загрузка файлов!');
            }

        } else {
            show_error('Ошибка! Данного раздела не существует!');
        }

        echo '<a href="/load/top">Топ файлов</a> / ';
        echo '<a href="/load/search">Поиск</a>';

        if (empty($cats['closed'])) {
            echo ' / <a href="/load/add?cid='.$cid.'">Добавить файл</a>';
        }
        echo '<br />';
    } else {
        App::redirect("/load");
    }
break;

############################################################################################
##                                    Просмотр файла                                      ##
############################################################################################
case 'view':

    $downs = Category::raw_query("SELECT d.*, c.name cats_name, c.folder cats_folder, c2.id subcats_id, c2.name subcats_name FROM downs d
          LEFT JOIN cats c ON d.category_id=c.id
          LEFT JOIN cats c2 ON c.parent = c2.id
        WHERE d.`id`=:id LIMIT 1;",
        ['id' => $id]
    )->find_one();

    if (!empty($downs)) {
        if (!empty($downs['active']) || $downs['user'] == App::getUsername()) {

            if (is_admin([101, 102])) {
                echo ' <a href="/admin/load?act=editdown&amp;cid='.$downs['category_id'].'&amp;id='.$id.'">Редактировать</a> / ';
                echo '<a href="/admin/load?act=movedown&amp;cid='.$downs['category_id'].'&amp;id='.$id.'">Переместить</a>';
            }

            //show_title($downs['title']);
            //Setting::get('description') =  strip_str($downs['text']);

            $folder = $downs['cats_folder'] ? $downs['cats_folder'].'/' : '';
?>
            <ol class="breadcrumb">
                <li><a href="/load">Категории</a></li>

                <?php if ($downs['subcats_id']): ?>
                    <li><a href="/load/down?cid=<?= $downs['subcats_id'] ?>"><?= $downs['subcats_name'] ?></a></li>
                <?php endif; ?>

                <li><a href="/load/down?cid=<?= $downs['category_id'] ?>"><?= $downs['cats_name'] ?></a></li>
                <li class="active"><?= $downs['title'] ?></li>
                <li class="active"><a href="/load/rss?id=<?= $id ?>">RSS-лента</a></li>
            </ol>
  <?php
            if (empty($downs['active']) && $downs['user'] == App::getUsername()){
                echo '<div class="info"><b>Внимание!</b> Данная загрузка добавлена, но еще требует модераторской проверки<br />';
                echo '<i class="fa fa-pencil"></i> <a href="/load/add?act=view&amp;id='.$id.'">Перейти к редактированию</a></div><br />';
            }

            $ext = getExtension($downs['link']);

            if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                echo '<a href="/uploads/files/'.$folder.$downs['link'].'" class="gallery">'.resize_image('uploads/files/'.$folder, $downs['link'], Setting::get('previewsize'), ['alt' => $downs['title']]).'</a><br />';
            }

            echo '<div class="message">'.App::bbCode($downs['text']).'</div><br />';

            $poster = '';
            if (!empty($downs['screen']) && file_exists(HOME.'/uploads/screen/'.$folder.$downs['screen'])) {
                $poster = ' poster="/uploads/screen/'.$folder.$downs['screen'].'"';

                if ($ext != 'mp4') {
                    echo 'Скриншот:<br />';
                    echo '<a href="/uploads/screen/'.$folder.$downs['screen'].'" class="gallery">'.resize_image('uploads/screen/'.$folder, $downs['screen'], Setting::get('previewsize'), ['alt' => $downs['title']]).'</a><br /><br />';
                }
            }

            if (!empty($downs['author'])) {
                echo 'Автор файла: '.$downs['author'];

                if (!empty($downs['site'])) {
                    echo ' (<a href="'.$downs['site'].'">'.$downs['site'].'</a>)';
                }
                echo '<br />';
            }

            if (!empty($downs['site']) && empty($downs['author'])) {
                echo 'Сайт автора: <a href="'.$downs['site'].'">'.$downs['site'].'</a><br />';
            }

            echo 'Добавлено: '.profile($downs['user']).' ('.date_fixed($downs['time']).')<hr />';

            // -----------------------------------------------------------//
            if (!empty($downs['link']) && file_exists(HOME.'/uploads/files/'.$folder.$downs['link'])) {

                if ($ext == 'mp3' || $ext == 'mp4') {?>

                    <?php if ($ext == 'mp3') { ?>
                        <audio src="/uploads/files/<?= $folder.$downs['link'] ?>"></audio><br/>
                    <?php } ?>

                    <?php if ($ext == 'mp4') { ?>
                        <video width="640" height="360" style="width: 100%; height: 100%;" src="/uploads/files/<?= $folder.$downs['link'] ?>" <?= $poster ?>></video>
                    <?php } ?>

                <?php
                }

                if ($ext == 'zip') {
                    echo '<i class="fa fa-archive"></i> <b><a href="/load/zip?id='.$id.'">Просмотреть архив</a></b><br />';
                }

                $filesize = (!empty($downs['link'])) ? read_file(HOME.'/uploads/files/'.$folder.$downs['link']) : 0;

                if (is_user()) {
                    echo '<i class="fa fa-download"></i> <b><a href="/load/down?act=load&amp;id='.$id.'">Скачать</a></b>  ('.$filesize.')<br />';
                } else {
                    echo '<div class="form">';
                    echo '<form action="/load/down?act=load&amp;id='.$id.'" method="post">';

                    echo 'Проверочный код:<br /> ';
                    echo '<img src="/captcha" onclick="this.src=\'/captcha?\'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;" alt="" /><br />';
                    echo '<input name="protect" size="6" maxlength="6" />';
                    echo '<input type="submit" value="Скачать ('.$filesize.')" /></form>';
                    echo '<em>Чтобы не вводить код при каждом скачивании, советуем <a href="/register">зарегистрироваться</a></em></div><br />';
                }

                echo '<i class="fa fa-comment"></i> <b><a href="/load/down?act=comments&amp;id='.$id.'">Комментарии</a></b> ('.$downs['comments'].') ';
                echo '<a href="/load/down?act=end&amp;id='.$id.'">&raquo;</a><br />';

                $rating = (!empty($downs['rated'])) ? round($downs['rating'] / $downs['rated'], 1) : 0;
                echo '<br />Рейтинг: '.rating_vote($rating).'<br />';
                echo 'Всего голосов: <b>'.$downs['rated'].'</b><br /><br />';

                if (is_user()) {
                    echo '<form action="/load/down?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo '<select name="score">';
                    echo '<option value="5">Отлично</option>';
                    echo '<option value="4">Хорошо</option>';
                    echo '<option value="3">Нормально</option>';
                    echo '<option value="2">Плохо</option>';
                    echo '<option value="1">Отстой</option>';
                    echo '</select>';
                    echo '<input type="submit" value="Oценить" /></form>';
                }

                echo 'Всего скачиваний: <b>'.$downs['loads'].'</b><br />';
                if (!empty($downs['last_load'])) {
                    echo 'Последнее скачивание: '.date_fixed($downs['last_load']).'<br />';
                }

                if (is_user()) {
                    echo '<br />Скопировать адрес:<br />';
                    echo '<input name="text" size="40" value="'.Setting::get('home').'/uploads/files/'.$folder.$downs['link'].'" /><br />';
                }

                echo '<br />';
            } else {
                show_error('Файл еще не загружен!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?cid='.$downs['category_id'].'">'.$downs['cats_name'].'</a><br />';

        } else {
            show_error('Ошибка! Данный файл еще не проверен модератором!');
        }
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }
break;

############################################################################################
##                                     Скачивание файла                                   ##
############################################################################################
case 'load':

    $protect = check(Request::input('protect'));

    if (is_user() || $protect == $_SESSION['protect']) {

        $downs = DB::run() -> queryFetch("SELECT downs.*, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id`=? LIMIT 1;", [$id]);

        if (!empty($downs)) {
            if (!empty($downs['active'])) {

                $folder = $downs['folder'] ? $downs['folder'].'/' : '';

                if (file_exists('uploads/files/'.$folder.$downs['link'])) {
                    $queryloads = DB::run() -> querySingle("SELECT ip FROM loads WHERE down=? AND ip=? LIMIT 1;", [$id, App::getClientIp()]);
                    if (empty($queryloads)) {
                        $expiresloads = SITETIME + 3600 * Setting::get('expiresloads');

                        DB::run() -> query("DELETE FROM loads WHERE time<?;", [SITETIME]);
                        DB::run() -> query("INSERT INTO loads (down, ip, time) VALUES (?, ?, ?);", [$id, App::getClientIp(), $expiresloads]);
                        DB::run() -> query("UPDATE downs SET loads=loads+1, last_load=? WHERE id=?", [SITETIME, $id]);
                    }

                    App::redirect("/uploads/files/".$folder.$downs['link']);
                } else {
                    show_error('Ошибка! Файла для скачивания не существует!');
                }
            } else {
                show_error('Ошибка! Данный файл еще не проверен модератором!');
            }
        } else {
            show_error('Ошибка! Данного файла не существует!');
        }
    } else {
        show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                       Оценка файла                                     ##
############################################################################################
case 'vote':

    $uid = check($_GET['uid']);
    if (isset($_POST['score'])) {
        $score = abs(intval($_POST['score']));
    } else {
        $score = 0;
    }

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if ($score > 0 && $score <= 5) {
                $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

                if (!empty($downs)) {
                    if (!empty($downs['active'])) {
                        if (App::getUsername() != $downs['user']) {
                            $queryrated = DB::run() -> querySingle("SELECT `id` FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user`=? LIMIT 1;", ['down', $id, App::getUsername()]);

                            if (empty($queryrated)) {
                                $expiresrated = SITETIME + 3600 * Setting::get('expiresrated');

                                DB::run() -> query("DELETE FROM `pollings` WHERE relate_type=? AND `time`<?;", ['down', SITETIME]);
                                DB::run() -> query("INSERT INTO `pollings` (relate_type, `relate_id`, `user`, `time`) VALUES (?, ?, ?, ?);", ['down', $id, App::getUsername(), $expiresrated]);
                                DB::run() -> query("UPDATE `downs` SET `rating`=`rating`+?, `rated`=`rated`+1 WHERE `id`=?", [$score, $id]);

                                echo '<b>Спасибо! Ваша оценка "'.$score.'" принята!</b><br />';
                                echo 'Всего оценивало: '.($downs['rated'] + 1).'<br />';
                                echo 'Средняя оценка: '.round(($downs['rating'] + $score) / ($downs['rated'] + 1), 1).'<br /><br />';
                            } else {
                                show_error('Ошибка! Вы уже оценивали данный файл!');
                            }
                        } else {
                            show_error('Ошибка! Нельзя голосовать за свой файл!');
                        }
                    } else {
                        show_error('Ошибка! Данный файл еще не проверен модератором!');
                    }
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Необходимо поставить оценку от 1 до 5 включительно!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, для голосования за файлы, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                        Комментарии                                     ##
############################################################################################
case 'comments':

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($downs)) {
        if (!empty($downs['active'])) {
            //Setting::get('newtitle') = 'Комментарии - '.$downs['title'];

            echo '<i class="fa fa-file-o"></i> <b><a href="/load/down?act=view&amp;id='.$id.'">'.$downs['title'].'</a></b><br /><br />';

            echo '<a href="/load/down?act=comments&amp;id='.$id.'&amp;rand='.mt_rand(100, 999).'">Обновить</a> / <a href="/load/rss?id='.$id.'">RSS-лента</a><hr />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['down', $id]);
            $page = App::paginate(Setting::get('downcomm'), $total);

            if ($total > 0) {


                $is_admin = is_admin();
                if ($is_admin) {
                    echo '<form action="/load/down?act=del&amp;id='.$id.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';
                }

                $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` ASC LIMIT ".$page['offset'].", ".Setting::get('downcomm').";", ['down', $id]);

                while ($data = $querycomm -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['user']).'</div>';

                    if ($is_admin) {
                        echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'" /></span>';
                    }

                    echo '<b>'.profile($data['user']).'</b> <small>('.date_fixed($data['time']).')</small><br />';
                    echo user_title($data['user']).' '.user_online($data['user']).'</div>';

                    if (!empty(App::getUsername()) && App::getUsername() != $data['user']) {
                        echo '<div class="right">';
                        echo '<a href="/load/down?act=reply&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Отв</a> / ';
                        echo '<a href="/load/down?act=quote&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Цит</a> / ';
                        echo '<noindex><a href="/load/down?act=spam&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете факт спама?\')" rel="nofollow">Спам</a></noindex></div>';
                    }

                    if (App::getUsername() == $data['user'] && $data['time'] + 600 > SITETIME) {
                        echo '<div class="right"><a href="/load/down?act=edit&amp;id='.$id.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';
                    }

                    echo '<div class="message">'.App::bbCode($data['text']).'<br />';

                    if (is_admin()) {
                        echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                    }
                    echo '</div>';
                }

                if ($is_admin) {
                    echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
                }

                App::pagination($page);
            } else {
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                echo '<div class="form">';
                echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<b>Сообщение:</b><br />';
                echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                echo '<input type="submit" value="Написать" /></form></div><br />';

                echo '<a href="/rules">Правила</a> / ';
                echo '<a href="/smiles">Смайлы</a> / ';
                echo '<a href="/tags">Теги</a><br /><br />';
            } else {
                show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
            }
        } else {
            show_error('Ошибка! Данный файл еще не проверен модератором!');
        }
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=view&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'add':

    $uid = check($_GET['uid']);
    $msg = check($_POST['msg']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {

                $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

                if (!empty($downs)) {
                    if (!empty($downs['active'])) {
                        if (Flood::isFlood(App::getUserId())) {

                            $msg = antimat($msg);

                            DB::run() -> query("INSERT INTO `comments` (relate_type, `relate_category_id`, `relate_id`, `text`, `user`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", ['down',$downs['category_id'], $id, $msg, App::getUsername(), SITETIME, App::getClientIp(), App::getUserAgent()]);

                            DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` DESC LIMIT ".Setting::get('maxdowncomm').") AS del);", ['down', $id, 'down', $id]);

                            DB::run() -> query("UPDATE `downs` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
                            DB::run() -> query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

                            App::setFlash('success', 'Сообщение успешно добавлено!');
                            App::redirect("/load/down?act=end&id=$id");
                        } else {
                            show_error('Антифлуд! Разрешается отправлять сообщения раз в '.Flood::getPeriod().' секунд!');
                        }
                    } else {
                        show_error('Ошибка! Данный файл еще не проверен модератором!');
                    }
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            $data = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['down', $pid]);

            if (!empty($data)) {
                $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [5, $pid]);

                if (empty($queryspam)) {
                    if (Flood::isFlood(App::getUserId())) {
                        DB::run() -> query("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [5, $data['id'], App::getUsername(), $data['user'], $data['text'], $data['time'], SITETIME, Setting::get('home').'/load/down?act=comments&amp;id='.$id.'&amp;page='.$page]);

                        App::setFlash('success', 'Жалоба успешно отправлена!');
                        App::redirect("/load/down?act=comments&id=$id&page=$page");
                    } else {
                        show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.Flood::getPeriod().' секунд!');
                    }
                } else {
                    show_error('Ошибка! Жалоба на данное сообщение уже отправлена!');
                }
            } else {
                show_error('Ошибка! Выбранное вами сообщение для жалобы не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы подать жалобу, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

    $pid = abs(intval($_GET['pid']));

    echo '<b><big>Ответ на сообщение</big></b><br /><br />';

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['down', $pid]);

        if (!empty($post)) {
            echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post['user']).'</b> '.user_title($post['user']).' '.user_online($post['user']).' <small>('.date_fixed($post['time']).')</small></div>';
            echo '<div>Сообщение: '.App::bbCode($post['text']).'</div><hr />';

            echo '<div class="form">';
            echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Сообщение:<br />';
            echo '<textarea cols="25" rows="5" name="msg" id="msg">[b]'.$post['user'].'[/b], </textarea><br />';
            echo '<input type="submit" value="Ответить" /></form></div><br />';
        } else {
            show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

    $pid = abs(intval($_GET['pid']));

    echo '<b><big>Цитирование</big></b><br /><br />';
    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? LIMIT 1;", ['down', $pid]);

        if (!empty($post)) {
            echo '<div class="form">';
            echo '<form action="/load/down?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Сообщение:<br />';
            echo '<textarea cols="25" rows="5" name="msg" id="msg">[quote][b]'.$post['user'].'[/b] ('.date_fixed($post['time']).')'."\r\n".$post['text'].'[/quote]'."\r\n".'</textarea><br />';
            echo '<input type="submit" value="Цитировать" /></form></div><br />';
        } else {
            show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

    //Setting::get('newtitle') = 'Редактирование сообщения';

    $pid = abs(intval($_GET['pid']));

    if (is_user()) {
        $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['down', $pid, App::getUsername()]);

        if (!empty($post)) {
            if ($post['time'] + 600 > SITETIME) {

                echo '<i class="fa fa-pencil"></i> <b>'.$post['user'].'</b> <small>('.date_fixed($post['time']).')</small><br /><br />';

                echo '<div class="form">';
                echo '<form action="/load/down?act=editpost&amp;id='.$post['relate_id'].'&amp;pid='.$pid.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br />';
                echo '<textarea cols="25" rows="5" name="msg" id="msg">'.$post['text'].'</textarea><br />';
                echo '<input type="submit" value="Редактировать" /></form></div><br />';
            } else {
                show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
            }
        } else {
            show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'editpost':

    $uid = check($_GET['uid']);
    $pid = abs(intval($_GET['pid']));
    $msg = check($_POST['msg']);

    if (is_user()) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {
                $post = DB::run() -> queryFetch("SELECT * FROM `comments` WHERE relate_type=? AND `id`=? AND `user`=? LIMIT 1;", ['down', $pid, App::getUsername()]);

                if (!empty($post)) {
                    if ($post['time'] + 600 > SITETIME) {

                        $msg = antimat($msg);

                        DB::run() -> query("UPDATE `comments` SET `text`=? WHERE relate_type=? AND `id`=?", [$msg, 'down', $pid]);

                        App::setFlash('success', 'Сообщение успешно отредактировано!');
                        App::redirect("/load/down?act=comments&id=$id&page=$page");
                    } else {
                        show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                    }
                } else {
                    show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое сообщение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=edit&amp;id='.$id.'&amp;pid='.$pid.'&amp;page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    if (isset($_POST['del'])) {
        $del = intar($_POST['del']);
    } else {
        $del = 0;
    }

    if (is_admin()) {
        if ($uid == $_SESSION['token']) {
            if (!empty($del)) {
                $del = implode(',', $del);

                $delcomments = DB::run() -> exec("DELETE FROM `comments` WHERE relate_type='down' AND `id` IN (".$del.") AND `relate_id`=".$id.";");
                DB::run() -> query("UPDATE `downs` SET `comments`=`comments`-? WHERE `id`=?;", [$delcomments, $id]);

                App::setFlash('success', 'Выбранные комментарии успешно удалены!');
                App::redirect("/load/down?act=comments&id=$id&page=$page");
            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?act=comments&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", ['down', $id]);

    if (!empty($query)) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = ceil($total_comments / Setting::get('downcomm'));

        App::redirect("/load/down?act=comments&id=$id&page=$end");
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view(Setting::get('themes').'/foot');
