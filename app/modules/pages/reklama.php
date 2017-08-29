<?php
App::view(Setting::get('themes').'/index');

//show_title('Реклама на сайте');

if (!empty(Setting::get('rekusershow'))) {
switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################

case 'index':

    //Setting::get('newtitle') = 'Список всех ссылок';

    $total = RekUser::where_gt('time', SITETIME)->count();

    $page = App::paginate(Setting::get('rekuserpost'), $total);

    if ($total > 0) {

        $reklama = RekUser::where_gt('time', SITETIME)
            ->limit(Setting::get('rekuserpost'))
            ->offset($page['offset'])
            ->order_by_desc('time')
            ->find_many();

        foreach($reklama as $data) {
            echo '<div class="b">';
            echo '<i class="fa fa-check-circle"></i> ';
            echo '<b><a href="'.$data['site'].'">'.$data['name'].'</a></b> ('.profile($data['user']).')</div>';

            echo 'Истекает: '.date_fixed($data['time']).'<br>';

            if (! empty($data['color'])) {
                echo 'Цвет: <span style="color:'.$data['color'].'">'.$data['color'].'</span>, ';
            } else {
                echo 'Цвет: нет, ';
            }

            if (! empty($data['bold'])) {
                echo 'Жирность: есть<br>';
            } else {
                echo 'Жирность: нет<br>';
            }
        }

        App::pagination($page);

        echo 'Всего ссылок: <b>'.$total.'</b><br><br>';
    } else {
        App::showError('В данный момент рекламных ссылок еще нет!');
    }

    echo '<i class="fa fa-money"></i> <a href="/reklama/create">Купить рекламу</a><br>';
    break;

############################################################################################
##                                   Добавление ссылки                                    ##
############################################################################################
case 'create':

    if (is_user()) {
        if (App::user('point') >= 50) {

            if (Request::isMethod('post')) {
                $token = !empty($_POST['token']) ? check($_POST['token']) : 0;
                $site = isset($_POST['site']) ? check($_POST['site']) : '';
                $name = isset($_POST['name']) ? check($_POST['name']) : '';
                $color = isset($_POST['color']) ? check($_POST['color']) : '';
                $bold = (empty($_POST['bold'])) ? 0 : 1;
                $provkod = isset($_POST['provkod']) ? check(strtolower($_POST['provkod'])) : '';

                $validation = new Validation();

                $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                    -> addRule('max', [App::user('point'), 50], 'Для покупки рекламы вам необходимо набрать '.points(50).'!')
                    -> addRule('equal', [$provkod, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!')
                    -> addRule('regex', [$site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu'], 'Недопустимый адрес сайта!. Разрешены символы [а-яa-z0-9_-.?=#/]!', true)
                    -> addRule('string', $site, 'Слишком длинный или короткий адрес ссылки!', true, 5, 50)
                    -> addRule('string', $name, 'Слишком длинное или короткое название ссылки!', true, 5, 35)
                    -> addRule('regex', [$color, '|^#+[A-f0-9]{6}$|'], 'Недопустимый формат цвета ссылки! (пример #ff0000)', false);

                if ($validation->run()) {

                    RekUser::where_lt('time', SITETIME)->delete_many();

                    $total = RekUser::where_gt('time', SITETIME)->count();

                    if ($total < Setting::get('rekusertotal')) {

                        $rekuser = RekUser::where('user', App::getUsername())->find_one();

                        if (empty($rekuser)) {
                            $price = Setting::get('rekuserprice');

                            if (!empty($color)) {
                                $price = $price + Setting::get('rekuseroptprice');
                            }

                            if (!empty($bold)) {
                                $price = $price + Setting::get('rekuseroptprice');
                            }

                            if (App::user('money') >= $price) {

                                $reklama = RekUser::create();
                                $reklama->set([
                                    'site'  => $site,
                                    'name'  => $name,
                                    'color' => $color,
                                    'bold' => $bold,
                                    'user' => App::getUsername(),
                                    'time' => SITETIME + (Setting::get('rekusertime') * 3600),
                                ])->save();

                                $user = User::find_one(App::getUserId());
                                $user->money -= $price;
                                $user->save();

                                save_advertuser();

                                App::setFlash('success', 'Рекламная ссылка успешно размещена (Cписано: '.moneys($price).')');
                                App::redirect("/reklama");

                            } else {
                                App::showError('Ошибка! Для покупки рекламы у вас недостаточно денег!');
                            }
                        } else {
                            App::showError('Ошибка! Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
                        }
                    } else {
                        App::showError('Ошибка! В данный момент нет свободных мест для размещения рекламы!');
                    }
                } else {
                    App::showError($validation->getErrors());
                }
            }

            $total = RekUser::where_gt('time', SITETIME)->count();

            if ($total < Setting::get('rekusertotal')) {

                $rekuser = RekUser::where('user', App::getUsername())->where_gt('time', SITETIME)->find_one();

                if (empty($rekuser)) {
                    echo 'У вас в наличии: <b>'.moneys(App::user('money')).'</b><br><br>';

                    echo '<div class="form">';
                    echo '<form method="post" action="/reklama/create">';
                    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                    echo 'Адрес сайта:<br>';
                    echo '<input name="site" type="text" value="http://" maxlength="50"><br>';

                    echo 'Название ссылки:<br>';
                    echo '<input name="name" type="text" maxlength="35"><br>';

                    echo 'Код цвета:';

                    if (file_exists(BASEDIR.'/modules/services/colors.php')) {
                        echo ' <a href="/services/colors">(?)</a>';
                    }
                    echo '<br>';
                    echo '<input name="color" type="text" maxlength="7"><br>';

                    echo 'Жирность: ';
                    echo '<input name="bold" type="checkbox" value="1"><br>';

                    echo 'Проверочный код:<br>';
                    echo '<img src="/captcha" alt=""><br>';
                    echo '<input name="provkod" size="6" maxlength="6"><br>';

                    echo '<br><input value="Купить" type="submit"></form></div><br>';

                    echo 'Стоимость размещения ссылки '.moneys(Setting::get('rekuserprice')).' за '.Setting::get('rekusertime').' часов<br>';
                    echo 'Цвет и жирность опционально, стоимость каждой опции '.moneys(Setting::get('rekuseroptprice')).'<br>';
                    echo 'Ссылка прокручивается на всех страницах сайта с другими ссылками пользователей<br>';
                    echo 'В названии ссылки запрещено использовать любые ненормативные и матные слова<br>';
                    echo 'Адрес ссылки не должен направлять на прямое скачивание какого-либо контента<br>';
                    echo 'Запрещены ссылки на сайты с алярмами и порно<br>';
                    echo 'За нарушение правил предусмотрено наказание в виде строгого бана<br><br>';

                } else {
                    App::showError('Ошибка! Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
                }
            } else {
                App::showError('В данный момент нет свободных мест для размещения рекламы!');
            }
        } else {
            App::showError('Ошибка! Для покупки рекламы вам необходимо набрать '.points(50).'!');
        }
    } else {
        App::showError('Для покупки рекламы необходимо авторизоваться');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/reklama">Вернуться</a><br>';
break;

endswitch;

} else {
    App::showError('Показ и размещение рекламы запрещено администрацией сайта!');
}

App::view(Setting::get('themes').'/foot');
