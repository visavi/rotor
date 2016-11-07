<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

if (is_admin()){

show_title('Доска объявлений');
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
if ($act == 'index') {

    $is_admin = is_admin([101,102]);

    if (file_exists(STORAGE."/board/database.dat")) {
        $lines = file(STORAGE."/board/database.dat");
        $total = count($lines);

        if ($total>0) {

            if ($is_admin) {echo '<form action="/admin/board?act=delrub&amp;uid='.$_SESSION['token'].'" method="post">';}

            foreach($lines as $key=>$boardval){
                $data = explode("|", $boardval);

                $totalboard = counter_string(STORAGE."/board/$data[2].dat");

                echo '<div class="b"><i class="fa fa-folder-open"></i> ';
                echo '<b><a href="/admin/board?act=board&amp;id='.$data[2].'">'.$data[0].'</a></b> ('.(int)$totalboard.')';

                if ($is_admin){
                    echo '<br /><input type="checkbox" name="del[]" value="'.$key.'" /> ';

                    if ($key != 0){echo '<a href="/admin/board?act=move&amp;id='.$key.'&amp;where=0&amp;uid='.$_SESSION['token'].'">Вверх</a> / ';} else {echo 'Вверх / ';}
                    if ($total > ($key+1)){echo '<a href="/admin/board?act=move&amp;id='.$key.'&amp;where=1&amp;uid='.$_SESSION['token'].'">Вниз</a>';} else {echo 'Вниз';}
                    echo ' / <a href="/admin/board?act=edit&amp;id='.$key.'">Редактировать</a>';
                }

                echo '</div>';

                echo '<div>'.$data[1].'</div>';
            }

            if ($is_admin) {echo '<br /><input type="submit" value="Удалить выбранное" /></form>';}

            echo '<p>Всего рубрик: <b>'.(int)$total.'</b></p>';

        } else {show_error('Доска объявлений пуста, рубрики еще не созданы!');}
    } else {show_error('Доска объявлений пуста, рубрики еще не созданы!');}

    if ($is_admin) {echo '<i class="fa fa-pencil"></i> <a href="/admin/board?act=add">Добавить</a><br />';}
}

############################################################################################
##                                   Просмотр рубрики                                     ##
############################################################################################
if ($act=="board")  {

    if ($id!=""){

        $string = search_string(STORAGE."/board/database.dat", $id, 2);
        if ($string) {

            $config['header'] = $string[0];
            $config['subheader'] = $string[1];

            echo '<a href="/admin/board">Объявления</a> / ';
            echo '<a href="/board?act=new&amp;id='.$id.'">Добавить</a> / ';
            echo '<a href="/board?act=board&amp;id='.$id.'">Обзор</a><br /><hr />';

            if (file_exists(STORAGE."/board/$id.dat")){
                $lines = file(STORAGE."/board/$id.dat");
                $lines = array_reverse($lines);
                $total = count($lines);

                if ($total>0) {
                    echo '<form action="/admin/board?act=deltop&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    if ($start < 0 || $start > $total){$start = 0;}
                    if ($total < $start + $config['boardspost']){ $end = $total; }
                    else {$end = $start + $config['boardspost']; }
                    for ($i = $start; $i < $end; $i++){

                    $data = explode("|",$lines[$i]);

                    $num = $total - $i - 1;

                    if (utf_strlen($data[2])>100) {
                    $data[2] = utf_substr($data[2],0,100); $data[2].="...";
                    }

                    echo '<div class="b">';

                    echo '<input type="checkbox" name="del[]" value="'.$num.'" /> ';

                    echo '<i class="fa fa-folder-open"></i> '.($i+1).'. ';
                    echo '<b><a href="/board?act=view&amp;id='.$id.'&amp;bid='.$data[5].'&amp;start='.$start.'">'.$data[0].'</a></b> ';
                    echo '(<small>'.date_fixed($data[3]).'</small>)</div>';
                    echo '<div>Текст объявления: '.$data[2].'<br />';
                    echo 'Автор объявления: '.profile($data[1]).'</div>';

                    }
                    echo '<br /><input type="submit" value="Удалить выбранное" /></form><br />';

                    page_strnavigation('/admin/board?act=board&amp;id='.$id.'&amp;', $config['boardspost'], $start, $total);

                    echo '<p>Всего объявлений: <b>'.(int)$total.'</b></p>';

                } else {show_error('Объявлений еще нет!');}
            } else {show_error('Объявлений еще нет!');}
        } else {show_error('Ошибка! Данной рубрики не существует!');}
    } else {show_error('Ошибка! Не выбрана рубрика для просмотра!');}

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board">Вернуться</a><br />';
}

############################################################################################
##                                  Подготовка к добавлению                               ##
############################################################################################
if ($act=="add") {

    if (is_admin([101,102])){

        echo '<b><big>Добавление рубрики</big></b><br /><br />';

        echo '<div class="form">';
        echo '<form action="/admin/board?act=addrub&amp;uid='.$_SESSION['token'].'" method="post">';
        echo 'Название: <br /><input type="text" name="zag" /><br />';
        echo 'Описание: <br /><input type="text" name="msg" /><br />';
        echo '<input type="submit" value="Добавить" /></form></div><br />';

    } else {show_error('Ошибка! Добавлять рубрики могут только администраторы!');}

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board">Вернуться</a><br />';
}

############################################################################################
##                                         Добавление                                     ##
############################################################################################
if ($act=="addrub") {

    $uid = check($_GET['uid']);
    $zag = check($_POST['zag']);
    $msg = check($_POST['msg']);

    if (is_admin([101,102])){
        if ($uid==$_SESSION['token']){
            if (utf_strlen(trim($zag))>=3 && utf_strlen($zag)<50){
                if (utf_strlen(trim($msg))>=5 && utf_strlen($msg)<50){

                    $unifile = unifile(STORAGE."/board/database.dat", 2);

                    $text = no_br($zag.'|'.$msg.'|'.$unifile.'|');

                    write_files(STORAGE."/board/database.dat", "$text\r\n", 0, 0666);

                    notice('Новый раздел успешно создан!');
                    redirect("/admin/board");

                } else {show_error('Ошибка! Слишком длинное или короткое описание рубрики!');}
            } else {show_error('Ошибка! Слишком длинное или короткое название рубрики!');}
        } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}
    } else {show_error('Ошибка! Добавлять рубрики могут только администраторы!');}

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/board?act=add">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board">К объявлениям</a><br />';
}


############################################################################################
##                                    Редактирование                                      ##
############################################################################################
if ($act=="edit") {

    if (is_admin([101,102])){
        if ($id!==""){

            $file = file(STORAGE."/board/database.dat");
            if (isset($file[$id])){
            $data = explode("|", $file[$id]);

            echo '<b><big>Редактирование рубрики</big></b><br /><br />';

            echo '<div class="form"><form action="/admin/board?id='.$id.'&amp;act=addedit&amp;uid='.$_SESSION['token'].'" method="post">';

            echo 'Название: <br /><input type="text" name="zag" value="'.$data[0].'" /><br />';
            echo 'Описание: <br /><input type="text" name="msg" value="'.$data[1].'" /><br />';

            echo '<input type="submit" value="Изменить" /></form></div><br />';

            } else {show_error('Ошибка! Данной рубрики не существует!');}
        } else {show_error('Ошибка! Не выбрана рубрика для редактирования!');}
    } else {show_error('Ошибка! Редактировать рубрики могут только администраторы!');}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board?">Вернуться</a><br />';
}


############################################################################################
##                                 Изменение рубрики                                      ##
############################################################################################
if ($act=="addedit") {

$uid = check($_GET['uid']);
$zag = check($_POST['zag']);
$msg = check($_POST['msg']);

if (is_admin([101,102])){
    if ($uid==$_SESSION['token']){
        if ($id!==""){
            if (utf_strlen(trim($zag))>=3 && utf_strlen($zag)<50){
                if (utf_strlen(trim($msg))>=5 && utf_strlen($msg)<50){

                    $file = file(STORAGE."/board/database.dat");
                    if (isset($file[$id])){
                        $data = explode("|", $file[$id]);

                        $text = no_br($zag.'|'.$msg.'|'.$data[2].'|');

                        replace_lines(STORAGE."/board/database.dat", $id, $text);

                        notice('Раздел успешно изменен!');
                        redirect("/admin/board");

                    } else {show_error('Ошибка! Рубрики для редактирования не существует!');}
                } else {show_error('Ошибка! Слишком длинное или короткое описание рубрики!');}
            } else {show_error('Ошибка! Слишком длинное или короткое название рубрики!');}
        } else {show_error('Ошибка! Не выбрана рубрика для редактирования!');}
    } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}
} else {show_error('Ошибка! Редактировать рубрики могут только администраторы!');}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/board?act=add">Вернуться</a><br />';
echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board">К объявлениям</a><br />';
}

############################################################################################
##                                       Сдвиг рубрик                                     ##
############################################################################################
if ($act=="move"){

    $uid = check($_GET['uid']);
    $where = (isset($_REQUEST['where'])) ? abs(intval($_REQUEST['where'])) : "";

    if (is_admin([101,102])){
        if ($uid==$_SESSION['token']){
            if ($id!==""){
                if ($where!==""){

                    move_lines(STORAGE."/board/database.dat", $id, $where);

                    notice('Раздел успешно перемещен!');
                    redirect("/admin/board");

                } else {show_error('Ошибка! Не выбрано действие для сдвига!');}
            } else {show_error('Ошибка! Не выбрана строка для сдвига!');}
        } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}
    } else {show_error('Ошибка! Двигать рубрики могут только администраторы!');}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board">Вернуться</a><br />';
}


############################################################################################
##                                 Удаление рубрики                                       ##
############################################################################################
if ($act=="delrub") {

    $uid = check($_GET['uid']);
    $del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : "";

    if (is_admin([101,102])){
        if ($uid==$_SESSION['token']){
            if ($del!==""){

                $file = file(STORAGE."/board/database.dat");

                foreach($del as $val){
                $data = explode("|", $file[$val]);

                if(file_exists(STORAGE."/board/$data[2].dat")){
                unlink (STORAGE."/board/$data[2].dat");
                }}

                delete_lines(STORAGE."/board/database.dat", $del);

                notice('Раздел успешно удален!');
                redirect("/admin/board");

            } else {show_error('Ошибка удаления! Отсутствуют выбранные рубрики!');}
        } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}
    } else {show_error('Ошибка! Удалять рубрики могут только администраторы!');}

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board">Вернуться</a><br />';
}


############################################################################################
##                                 Удаление объявлений                                    ##
############################################################################################
if ($act=="deltop") {

    $uid = check($_GET['uid']);
    $del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : "";

    if ($uid==$_SESSION['token']){
        if ($id!=""){
            if ($del!==""){

            delete_lines(STORAGE."/board/$id.dat", $del);

            notice('Объявление успешно удалено!');
            redirect("/admin/board?act=board&id=$id&start=$start");

            } else {show_error('Ошибка! Отсутствуют выбранные объявления!');}
        } else {show_error('Ошибка! Не выбрана рубрика для удаления!');}
    } else {show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');}

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/board?act=board&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
}

//----------------------- Концовка -------------------------//
echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
