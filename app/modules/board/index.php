<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$bid = (isset($_GET['bid'])) ? abs(intval($_GET['bid'])) : 0;
$page = abs(intval(Request::input('page', 1)));

//show_title('Доска объявлений');

############################################################################################
##                                 Вывод перечня категорий                                ##
############################################################################################
if ($act == 'index') {

    if (file_exists(STORAGE."/board/database.dat")) {
        $lines = file(STORAGE."/board/database.dat");
        $total = count($lines);

        if ($total>0) {

            foreach($lines as $boardval){
                $data = explode("|", $boardval);

                $totalboard = counter_string(STORAGE."/board/$data[2].dat");

                echo '<div class="b"><i class="fa fa-folder-open"></i> ';
                echo '<b><a href="/board?act=board&amp;id='.$data[2].'">'.$data[0].'</a></b> ('.(int)$totalboard.')</div>';

                echo '<div>'.$data[1].'<br>';

                if($totalboard>0){
                    $fileboard = file(STORAGE."/board/$data[2].dat");
                    $lostlist = explode("|",end($fileboard));

                    if (utf_strlen($lostlist[0])>35) {$lostlist[0]=utf_substr($lostlist[0],0,30); $lostlist[0].="...";}

                    echo 'Тема: <a href="/board?act=view&amp;id='.$lostlist[6].'&amp;bid='.$lostlist[5].'">'.$lostlist[0].'</a><br>';

                    echo 'Объявление: '.profile($lostlist[1]).' <small>('.date_fixed($lostlist[3]).')</small>';

                } else {echo 'Рубрика пуста, объявлений нет!';}

                echo '</div>';
            }

            echo '<p>Всего рубрик: <b>'.(int)$total.'</b></p>';

        } else {show_error('Доска объявлений пуста, рубрики еще не созданы!');}
    } else {show_error('Доска объявлений пуста, рубрики еще не созданы!');}
}

############################################################################################
##                         Вывод объявлений в текущей категории                           ##
############################################################################################
if ($act == "board"){

    $string = search_string(STORAGE."/board/database.dat", $id, 2);
    if ($string) {
        //Setting::get('header') = $string[0];
        //Setting::get('header') = $string[1];

        echo '<a href="/board">Объявления</a> / ';
        echo '<a href="/board?act=new&amp;id='.$id.'">Добавить</a>';

        if (is_admin()){
            echo ' / <a href="/admin/board?act=board&amp;id='.$id.'">Управление</a>';
        }
        echo '<hr>';

        if (file_exists(STORAGE."/board/$id.dat")){
            $files = file(STORAGE."/board/$id.dat");
            //---------------Функция автоудаления--------------------//
            $newlines = [];
            foreach($files as $bkey=>$bvalue){
                $bdata = explode("|", $bvalue);
                if($bdata[4]<SITETIME){
                    $newlines[] = (int)$bkey;
                }
            }

            if(count($newlines)>0){
                delete_lines(STORAGE."/board/$id.dat", $newlines);
            }
            //------------------------------------------------------//
            $files = array_reverse($files);
            $total = count($files);

            $page = App::paginate(Setting::get('boardspost'), $total);
            if ($total>0) {

                if ($total < $page['offset'] + Setting::get('boardspost')){
                    $end = $total;
                } else {
                    $end = $page['offset'] + Setting::get('boardspost');
                }

                for ($i = $page['offset']; $i < $end; $i++){

                    $data = explode("|",$files[$i]);

                    if (utf_strlen($data[2])>100) {
                        $data[2] = utf_substr($data[2],0,100); $data[2].="...";
                    }

                    echo '<div class="b">';
                    echo '<i class="fa fa-folder-open"></i> '.($i+1).'. ';
                    echo '<b><a href="/board?act=view&amp;id='.$id.'&amp;bid='.$data[5].'&amp;page='.$page['current'].'">'.$data[0].'</a></b> ';
                    echo '<small>('.date_fixed($data[3]).')</small></div>';
                    echo 'Текст объявления: '.$data[2].'<br>';
                    echo 'Автор объявления: '.profile($data[1]).'<br>';
                }

                App::pagination($page);

                echo '<p>Всего объявлений: <b>'.(int)$total.'</b></p>';

            } else {show_error('Объявлений еще нет, будь первым!');}
        } else {show_error('Объявлений еще нет, будь первым!');}
    } else {show_error('Ошибка! Данной рубрики не существует!');}
}

############################################################################################
##                         Просмотр объявления в текущей категории                        ##
############################################################################################
if($act == "view"){

    if (file_exists(STORAGE."/board/$id.dat")){
        $string = search_string(STORAGE."/board/database.dat", $id, 2);
        if ($string) {

            $bstr = search_string(STORAGE."/board/$id.dat", $bid, 5);
            if ($bstr) {

                //Setting::get('header') = $bstr[0];

                echo '<a href="/board">Объявления</a> / ';
                echo '<a href="/board?act=board&amp;id='.$id.'">'.$string[0].'</a> / ';
                echo '<a href="/board?act=new&amp;id='.$id.'">Добавить</a><hr>';

                echo $bstr[2].'<br>';
                echo 'Автор: '.profile($bstr[1]).'<br>';
                echo 'Размещено:  '.date_fixed($bstr[3]).'<br>';
                echo '<small>Дата удаления: <b>'.date_fixed($bstr[4]).'</b></small><br><br>';

            } else {show_error('Ошибка! Данного объявления не существует!');}
        } else {show_error('Ошибка! Данной рубрики не существует!');}
    } else {show_error('Ошибка! Данной рубрики не существует!');}
}

############################################################################################
##                              Форма добавления объявления                               ##
############################################################################################
if ($act == "new"){

    //Setting::get('header') = 'Добавление объявления';

    if (is_user()){

        if (search_string(STORAGE."/board/database.dat", $id, 2)) {

            echo '<div class="form">';
            echo '<form action="/board?act=add&amp;id='.$id.'" method="post">';
            echo '<b>Заголовок:</b><br><input type="text" name="zag" maxlength="50"><br>';
            echo '<b>Объявление:</b><br><textarea cols="25" rows="3" name="msg"></textarea><br>';
            echo '<b>Срок показа:</b><br><select name="days">';

            for($i=5; $i<=Setting::get('boarddays'); $i=$i+5){
                echo '<option  value="'.$i.'">'.$i.' дней</option>';
            }

            echo '</select><br> (Максимальный срок показа -  <b>'.(int)Setting::get('boarddays').'</b> дней.)<br>';
            echo '<input type="submit" value="Добавить"></form></div><br>';

        } else {show_error('Ошибка! Данного раздела не существует!');}
    } else {show_login('Вы не авторизованы, чтобы добавить объявление, необходимо');}

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/board?act=board&amp;id='.$id.'">Вернуться</a><br>';
}

############################################################################################
##                                  Добавление объявления                                 ##
############################################################################################
if ($act == "add"){

    //Setting::get('header') = 'Добавление объявления';

    if (is_user()){
        if (search_string(STORAGE."/board/database.dat", $id, 2)) {

        $zag = check($_POST['zag']);
        $msg = check($_POST['msg']);
        $days = (int)$_POST['days'];

        if (utf_strlen(trim($zag))>=5 && utf_strlen($zag)<=50){
            if (utf_strlen(trim($msg))>=10 && utf_strlen($msg)<=1000){
                if ($days>0 && $days<=Setting::get('boarddays')){

                    $deltime = SITETIME + ($days * 86400);

                    $msg = no_br($msg);

                    $unifile = unifile(STORAGE."/board/$id.dat", 5);

                    $text = no_br($zag.'|'.App::getUsername().'|'.$msg.'|'.SITETIME.'|'.$deltime.'|'.$unifile.'|'.$id.'|');

                    write_files(STORAGE."/board/$id.dat", "$text\r\n", 0, 0666);

                    App::setFlash('success', 'Объявление успешно размещено!');
                    App::redirect("/board?act=board&id=$id");

                } else {show_error('Ошибка, не указано число дней показа объявления!');}
            } else {show_error('Слишком длинное или короткое объявление (Необходимо от 10 до 1000 символов)');}
        } else {show_error('Слишком длинный или короткий заголовок (Необходимо от 5 до 50 символов)');}
    } else {show_error('Ошибка! Данной рубрики не существует!');}
} else {show_login('Вы не авторизованы, чтобы добавить объявление, необходимо');}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/board?act=new&amp;id='.$id.'">Вернуться</a><br>';
}

echo '<i class="fa fa-home"></i> <a href="/">На главную</a><br>';

App::view(Setting::get('themes').'/foot');
