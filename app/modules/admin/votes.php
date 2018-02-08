<?php


    switch ($action):

        ############################################################################################
        ##                                    Пересчет счетчиков                                  ##
        ############################################################################################
        case 'rest':
            $uid = check($_GET['uid']);
            if ($uid == $_SESSION['token']) {
                if (isAdmin([101])) {
                    DB::update("UPDATE `vote` SET `count`=(SELECT SUM(`result`) FROM `voteanswer` WHERE `vote`.id=`voteanswer`.`vote_id`) WHERE `closed`=?;", [0]);

                    setFlash('success', 'Все данные успешно пересчитаны!');
                    redirect("/admin/votes");
                } else {
                    showError('Ошибка! Пересчитывать голосования могут только суперадмины!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                          История                                      ##
        ############################################################################################
        case 'history':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `vote` WHERE `closed`=? ORDER BY `time`;", [1]);
            $page = paginate(setting('allvotes'), $total);

            if ($total > 0) {

                $queryvote = DB::select("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('allvotes').";", [1]);

                while ($data = $queryvote -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-briefcase"></i> <b><a href="/votes/history?act=result&amp;id='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b><br>';

                    echo '<a href="/admin/votes?act=action&amp;do=open&amp;id='.$data['id'].'&amp;uid='.$_SESSION['token'].'">Открыть</a>';

                    if (isAdmin([101])) {
                        echo ' / <a href="/admin/votes?act=del&amp;id='.$data['id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
                    }

                    echo '</div>';
                    echo '<div>Создано: '.dateFixed($data['time']).'<br>';
                    echo 'Всего голосов: '.$data['count'].'</div>';
                }

                pagination($page);
            } else {
                showError('Голосований в архиве еще нет!');
            }

            echo '<i class="fa fa-chart-bar"></i> <a href="/admin/votes">Список голосований</a><br>';
        break;

    endswitch;


