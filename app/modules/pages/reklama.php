<?php
view(setting('themes').'/index');

//show_title('Реклама на сайте');

if (!empty(setting('rekusershow'))) {
switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################

case 'index':

    //setting('newtitle') = 'Список всех ссылок';

    $total = RekUser::where_gt('time', SITETIME)->count();

    $page = paginate(setting('rekuserpost'), $total);

    if ($total > 0) {

        $reklama = RekUser::where_gt('time', SITETIME)
            ->limit(setting('rekuserpost'))
            ->offset($page['offset'])
            ->order_by_desc('time')
            ->find_many();

        foreach($reklama as $data) {
            echo '<div class="b">';
            echo '<i class="fa fa-check-circle"></i> ';
            echo '<b><a href="'.$data['site'].'">'.$data['name'].'</a></b> ('.profile($data['user']).')</div>';

            echo 'Истекает: '.dateFixed($data['time']).'<br>';

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

        pagination($page);

        echo 'Всего ссылок: <b>'.$total.'</b><br><br>';
    } else {
        showError('В данный момент рекламных ссылок еще нет!');
    }

    echo '<i class="fa fa-money"></i> <a href="/reklama/create">Купить рекламу</a><br>';
    break;

############################################################################################
##                                   Добавление ссылки                                    ##
############################################################################################
case 'create':

    if (getUser()) {
        if (getUser('point') >= 50) {

            if (Request::isMethod('post')) {
                $token = !empty($_POST['token']) ? check($_POST['token']) : 0;
                $site = isset($_POST['site']) ? check($_POST['site']) : '';
                $name = isset($_POST['name']) ? check($_POST['name']) : '';
                $color = isset($_POST['color']) ? check($_POST['color']) : '';
                $bold = (empty($_POST['bold'])) ? 0 : 1;
                $provkod = isset($_POST['provkod']) ? check(strtolower($_POST['provkod'])) : '';

                $validation = new Validation();

                $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                    -> addRule('max', [getUser('point'), 50], 'Для покупки рекламы вам необходимо набрать '.plural(50, setting('scorename')).'!')
                    -> addRule('equal', [$provkod, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!')
                    -> addRule('regex', [$site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu'], 'Недопустимый адрес сайта!. Разрешены символы [а-яa-z0-9_-.?=#/]!', true)
                    -> addRule('string', $site, 'Слишком длинный или короткий адрес ссылки!', true, 5, 50)
                    -> addRule('string', $name, 'Слишком длинное или короткое название ссылки!', true, 5, 35)
                    -> addRule('regex', [$color, '|^#+[A-f0-9]{6}$|'], 'Недопустимый формат цвета ссылки! (пример #ff0000)', false);

                if ($validation->run()) {

                    RekUser::where_lt('time', SITETIME)->delete_many();

                    $total = RekUser::where_gt('time', SITETIME)->count();

                    if ($total < setting('rekusertotal')) {

                        $rekuser = RekUser::where('user', getUser('login'))->find_one();

                        if (empty($rekuser)) {
                            $price = setting('rekuserprice');

                            if (!empty($color)) {
                                $price = $price + setting('rekuseroptprice');
                            }

                            if (!empty($bold)) {
                                $price = $price + setting('rekuseroptprice');
                            }

                            if (getUser('money') >= $price) {

                                $reklama = RekUser::create();
                                $reklama->set([
                                    'site'  => $site,
                                    'name'  => $name,
                                    'color' => $color,
                                    'bold' => $bold,
                                    'user' => getUser('login'),
                                    'time' => SITETIME + (setting('rekusertime') * 3600),
                                ])->save();

                                $user = User::find_one(getUser('id'));
                                $user->money -= $price;
                                $user->save();

                                saveAdvertUser();

                                setFlash('success', 'Рекламная ссылка успешно размещена (Cписано: '.plural($price, setting('moneyname')).')');
                                redirect("/reklama");

                            } else {
                                showError('Ошибка! Для покупки рекламы у вас недостаточно денег!');
                            }
                        } else {
                            showError('Ошибка! Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
                        }
                    } else {
                        showError('Ошибка! В данный момент нет свободных мест для размещения рекламы!');
                    }
                } else {
                    showError($validation->getErrors());
                }
            }

            $total = RekUser::where_gt('time', SITETIME)->count();

            if ($total < setting('rekusertotal')) {

                $rekuser = RekUser::where('user', getUser('login'))->where_gt('time', SITETIME)->find_one();

                if (empty($rekuser)) {
                    echo 'У вас в наличии: <b>'.plural(getUser('money'), setting('moneyname')).'</b><br><br>';

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

                    echo 'Стоимость размещения ссылки '.plural(setting('rekuserprice'), setting('moneyname')).' за '.setting('rekusertime').' часов<br>';
                    echo 'Цвет и жирность опционально, стоимость каждой опции '.plural(setting('rekuseroptprice'), setting('moneyname')).'<br>';
                    echo 'Ссылка прокручивается на всех страницах сайта с другими ссылками пользователей<br>';
                    echo 'В названии ссылки запрещено использовать любые ненормативные и матные слова<br>';
                    echo 'Адрес ссылки не должен направлять на прямое скачивание какого-либо контента<br>';
                    echo 'Запрещены ссылки на сайты с алярмами и порно<br>';
                    echo 'За нарушение правил предусмотрено наказание в виде строгого бана<br><br>';

                } else {
                    showError('Ошибка! Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
                }
            } else {
                showError('В данный момент нет свободных мест для размещения рекламы!');
            }
        } else {
            showError('Ошибка! Для покупки рекламы вам необходимо набрать '.plural(50, setting('scorename')).'!');
        }
    } else {
        showError('Для покупки рекламы необходимо авторизоваться');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/reklama">Вернуться</a><br>';
break;

endswitch;

} else {
    showError('Показ и размещение рекламы запрещено администрацией сайта!');
}

view(setting('themes').'/foot');
