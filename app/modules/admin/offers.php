<?php


    switch ($action):


        ############################################################################################
        ##                          Удаление предложений и проблем                                ##
        ############################################################################################
        case 'del':

            $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::delete("DELETE FROM `offers` WHERE `id` IN (".$del.");");
                    DB::delete("DELETE FROM `comments` WHERE relate_type='offer' AND `relate_id` IN (".$del.");");
                    DB::delete("DELETE FROM `pollings` WHERE relate_type=? AND `relate_id` IN (".$del.");");

                    setFlash('success', 'Выбранные пункты успешно удалены!');
                    redirect("/admin/offers?type=$type&page=$page");
                } else {
                    showError('Ошибка! Отсутствуют выбранные предложения или проблемы!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?page='.$page.'">Вернуться</a><br>';
        break;

