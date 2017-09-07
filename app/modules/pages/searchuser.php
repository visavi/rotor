<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

//show_title('Поиск пользователей');

if (isUser()) {
switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':

        echo '<div class="form">';
        echo '<form method="post" action="/searchuser?act=search">';
        echo 'Логин пользователя:<br><input type="text" name="find">';
        echo '<input value="Поиск" type="submit"></form></div><br>';

        echo '<a href="/searchuser?act=sort&amp;q=1">0-9</a> / <a href="/searchuser?act=sort&amp;q=a">A</a> / <a href="/searchuser?act=sort&amp;q=b">B</a> / <a href="/searchuser?act=sort&amp;q=c">C</a> / <a href="/searchuser?act=sort&amp;q=d">D</a> / <a href="/searchuser?act=sort&amp;q=e">E</a> / <a href="/searchuser?act=sort&amp;q=f">F</a> / <a href="/searchuser?act=sort&amp;q=g">G</a> / <a href="/searchuser?act=sort&amp;q=h">H</a> / <a href="/searchuser?act=sort&amp;q=i">I</a> / <a href="/searchuser?act=sort&amp;q=j">J</a> / <a href="/searchuser?act=sort&amp;q=k">K</a> / <a href="/searchuser?act=sort&amp;q=l">L</a> / <a href="/searchuser?act=sort&amp;q=m">M</a> / <a href="/searchuser?act=sort&amp;q=n">N</a> / <a href="/searchuser?act=sort&amp;q=o">O</a> / <a href="/searchuser?act=sort&amp;q=p">P</a> / <a href="/searchuser?act=sort&amp;q=q">Q</a> / <a href="/searchuser?act=sort&amp;q=r">R</a> / <a href="/searchuser?act=sort&amp;q=s">S</a> / <a href="/searchuser?act=sort&amp;q=t">T</a> / <a href="/searchuser?act=sort&amp;q=u">U</a> / <a href="/searchuser?act=sort&amp;q=v">V</a> / <a href="/searchuser?act=sort&amp;q=w">W</a> / <a href="/searchuser?act=sort&amp;q=x">X</a> / <a href="/searchuser?act=sort&amp;q=y">Y</a> / <a href="/searchuser?act=sort&amp;q=z">Z</a><br><br>';

        echo 'Если результат поиска ничего не дал, тогда можно поискать по первым символам логина<br>';
        echo 'В этом случае будет выдан результат похожий на введенный вами запрос<br><br>';
    break;

    ############################################################################################
    ##                                  Сортировка профилей                                   ##
    ############################################################################################
    case 'sort':
        if (isset($_POST['q'])) {
            $q = check(strtolower($_POST['q']));
        } else {
            $q = check(strtolower($_GET['q']));
        }

        if (!empty($q)) {
            if ($q == 1) {
                $search = "RLIKE '^[-0-9]'";
            } else {
                $search = "LIKE '$q%'";
            }

            $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE lower(`login`) ".$search.";");
            $page = paginate(setting('usersearch'), $total);

            if ($total > 0) {

                $queryuser = DB::select("SELECT `login`, `point` FROM `users` WHERE lower(`login`) ".$search." ORDER BY `point` DESC LIMIT ".$page['offset'].", ".setting('usersearch').";");
                while ($data = $queryuser -> fetch()) {

                    echo userGender($data['login']).' <b>'.profile($data['login'], false, false).'</b> ';
                    echo userOnline($data['login']).' ('.plural($data['point'], setting('scorename')).')<br>';
                }

                pagination($page);

                echo 'Найдено совпадений: '.$total.'<br><br>';
            } else {
                showError('Совпадений не найдено!');
            }
        } else {
            showError('Ошибка! Не выбраны критерии поиска пользователей!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/searchuser">Вернуться</a><br>';
    break;

    ############################################################################################
    ##                                    Поиск пользователя                                  ##
    ############################################################################################
    case 'search':

        $find = check(strtolower($_POST['find']));

        if (utfStrlen($find)>=3 && utfStrlen($find)<=20) {
            $querysearch = DB::select("SELECT `login`, `point` FROM `users` WHERE lower(`login`) LIKE ? ORDER BY `point` DESC LIMIT ".setting('usersearch').";", ['%'.$find.'%']);

            $result = $querysearch -> fetchAll();
            $total = count($result);

            if ($total > 0) {
                foreach($result as $value) {
                    echo userGender($value['login']);

                    if ($find == $value['login']) {
                        echo '<b><big>'.profile($value['login'], '#ff0000').'</big></b> '.userOnline($value['login']).' ('.plural($value['point'], setting('scorename')).')<br>';
                    } else {
                        echo '<b>'.profile($value['login']).'</b> '.userOnline($value['login']).' ('.plural($value['point'], setting('scorename')).')<br>';
                    }
                }

                echo '<br>Найдено совпадений: <b>'.$total.'</b><br><br>';
            } else {
                showError('По вашему запросу ничего не найдено');
            }
        } else {
            showError('Ошибка! Слишком короткий или длинный запрос, от 3 до 20 символов!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/searchuser">Вернуться</a><br>';
    break;

endswitch;

} else {
    showError('Ошибка! Для поиска пользователей необходимо авторизоваться!');
}

view(setting('themes').'/foot');
