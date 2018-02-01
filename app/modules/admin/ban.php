<?php



        ############################################################################################
        ##                                    Разбан пользователя                                 ##
        ############################################################################################
        case 'razban':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if ($user['level'] == User::BANNED) {
                        if ($user['totalban'] > 0 && $user['timeban'] > SITETIME + 43200) {
                            $bancount = 1;
                        } else {
                            $bancount = 0;
                        }

                        DB::update("UPDATE `users` SET `ban`=?, `timeban`=?, `totalban`=`totalban`-?, `explainban`=? WHERE `login`=? LIMIT 1;", [0, 0, $bancount, 0, $uz]);

                        DB::insert("INSERT INTO `banhist` (`user`, `send`, `time`) VALUES (?, ?, ?);", [$uz, getUser('login'), SITETIME]);

                        setFlash('success', 'Аккаунт успешно разблокирован!');
                        redirect("/admin/ban?act=edit&uz=$uz");
                    } else {
                        showError('Ошибка! Данный аккаунт уже разблокирован!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
        break;

