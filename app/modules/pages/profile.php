<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Мой профиль');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	echo '<i class="fa fa-book"></i> ';
	echo '<a href="/user/'.App::getUsername().'">Моя анкета</a> / ';
	echo '<b>Мой профиль</b> / ';
	echo '<a href="/account">Мои данные</a> / ';
	echo '<a href="/setting">Настройки</a><hr />';

	echo '<div class="form">';
	echo '<form method="post" action="/profile?act=edit&amp;uid='.$_SESSION['token'].'">';

	echo '<div class="pull-right">';
	if (!empty($udata['picture']) && file_exists(HOME.'/uploads/photos/'.$udata['picture'])) {
		echo '<a href="/uploads/photos/'.$udata['picture'].'">';
		echo resize_image('uploads/photos/', $udata['picture'], $config['previewsize'], ['alt' => nickname($udata['login']), 'class' => 'img-responsive img-rounded']).'</a>';
		echo '<a href="/pictures">Изменить</a> / <a href="/pictures?act=del&amp;uid='.$_SESSION['token'].'">Удалить</a>';
	} else {
		echo '<img class="img-responsive img-rounded" src="/assets/img/images/photo.jpg" alt="Фото" />';
		echo '<a href="/pictures">Загрузить фото</a>';
	}
	echo '</div>';

	echo 'Имя:<br /><input name="name" maxlength="20" value="'.$udata['name'].'" /><br />';
	echo 'Страна:<br /><input name="country" maxlength="30" value="'.$udata['country'].'" /><br />';
	echo 'Откуда:<br /><input name="city" maxlength="50" value="'.$udata['city'].'" /><br />';
	echo 'ICQ:<br /><input name="icq" maxlength="10" value="'.$udata['icq'].'" /><br />';
	echo 'Skype:<br /><input name="skype" maxlength="32" value="'.$udata['skype'].'" /><br />';
	echo 'Сайт:<br /><input name="site" maxlength="50" value="'.$udata['site'].'" /><br />';
	echo 'Дата рождения (дд.мм.гггг):<br /><input name="birthday" maxlength="10" value="'.$udata['birthday'].'" /><br />';

	echo 'Пол:<br />';
	echo '<select name="gender">';
	$selected = ($udata['gender'] == 1) ? ' selected="selected"' : '';
	echo '<option value="1"'.$selected.'>Мужской</option>';
	$selected = ($udata['gender'] == 2) ? ' selected="selected"' : '';
	echo '<option value="2"'.$selected.'>Женский</option>';
	echo '</select><br />';

	echo 'О себе:<br />';
	echo '<textarea id="markItUp" cols="25" rows="5" name="info">'.$udata['info'].'</textarea><br />';

	echo '<input value="Изменить" type="submit" /></form></div><br />';
break;

############################################################################################
##                                       Изменение                                        ##
############################################################################################
case 'edit':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$name = (isset($_POST['name'])) ? check($_POST['name']) : '';
	$country = (isset($_POST['country'])) ? check($_POST['country']) : '';
	$city = (isset($_POST['city'])) ? check($_POST['city']) : '';
	$icq = (!empty($_POST['icq'])) ? check(str_replace('-', '', $_POST['icq'])) : '';
	$skype = (isset($_POST['skype'])) ? check(strtolower($_POST['skype'])) : '';
	$site = (isset($_POST['site'])) ? check($_POST['site']) : '';
	$birthday = (isset($_POST['birthday'])) ? check($_POST['birthday']) : '';
	$gender = (isset($_POST['gender'])) ? intval($_POST['gender']) : 0;
	$info = (isset($_POST['info'])) ? check($_POST['info']) : '';

	$validation = new Validation();

	$validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('regex', [$site, '#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u'], 'Недопустимый адрес сайта, необходим формата http://my_site.domen!', false)
		-> addRule('regex', [$birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#'], 'Недопустимый формат даты рождения, необходим формат дд.мм.гггг!', false)
		-> addRule('regex', [$icq, '#^[0-9]{5,10}$#'], 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!', false)
		-> addRule('regex', [$skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#'], 'Недопустимый формат Skype, только латинские символы от 6 до 32!', false)
		-> addRule('numeric', $gender, 'Вы не указали ваш пол!', true, 1, 2)
		-> addRule('string', $info, 'Слишком большая информация о себе, не более 1000 символов!',  true, 0, 1000);

	if ($validation->run()) {

		$name = utf_substr($name, 0, 20);
		$country = utf_substr($country, 0, 30);
		$city = utf_substr($city, 0, 50);

		DB::run() -> query("UPDATE `users` SET `name`=?, `country`=?, `city`=?, `icq`=?, `skype`=?, `site`=?, `birthday`=?, `gender`=?, `info`=? WHERE `login`=? LIMIT 1;", [$name, $country, $city, $icq, $skype, $site, $birthday, $gender, $info, $log]);

		notice('Ваш профиль успешно изменен!');
		redirect("/profile");

	} else {
		show_error($validation->getErrors());
	}

	echo'<i class="fa fa-arrow-circle-left"></i> <a href="/profile">Вернуться</a><br />';
break;

endswitch;

} else {
	show_login('Вы не авторизованы, чтобы изменять свои данные, необходимо');
}

App::view($config['themes'].'/foot');
