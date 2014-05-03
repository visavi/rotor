<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Мой профиль');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	echo '<img src="/images/img/document.gif" alt="image" /> ';
	echo '<a href="user.php">Моя анкета</a> / ';
	echo '<b>Мой профиль</b> / ';
	echo '<a href="account.php">Мои данные</a> / ';
	echo '<a href="setting.php">Настройки</a><hr />';

	echo '<div class="form">';
	echo '<form method="post" action="profile.php?act=edit&amp;uid='.$_SESSION['token'].'">';

	if (!empty($udata['users_picture']) && file_exists(BASEDIR.'/upload/photos/'.$udata['users_picture'])) {
		echo '<div class="imgright"><a href="/upload/photos/'.$udata['users_picture'].'">';
		echo resize_image('upload/photos/', $udata['users_picture'], $config['previewsize'], nickname($udata['users_login'])).'</a><br />';
		echo '<a href="pictures.php">Изменить</a>/<a href="pictures.php?act=del&amp;uid='.$_SESSION['token'].'">Удалить</a></div>';
	} else {
		echo '<div class="imgright">';
		echo '<img src="/images/img/photo.jpg" alt="Фото" />';
		echo '<br /><a href="pictures.php">Загрузить фото</a></div>';
	}

	echo 'Имя:<br /><input name="name" maxlength="20" value="'.$udata['users_name'].'" /><br />';
	echo 'Страна:<br /><input name="country" maxlength="30" value="'.$udata['users_country'].'" /><br />';
	echo 'Откуда:<br /><input name="city" maxlength="50" value="'.$udata['users_city'].'" /><br />';
	echo 'ICQ:<br /><input name="icq" maxlength="10" value="'.$udata['users_icq'].'" /><br />';
	echo 'Skype:<br /><input name="skype" maxlength="32" value="'.$udata['users_skype'].'" /><br />';
	echo 'Jabber:<br /><input name="jabber" maxlength="50" value="'.$udata['users_jabber'].'" /><br />';
	echo 'Сайт:<br /><input name="site" maxlength="50" value="'.$udata['users_site'].'" /><br />';
	echo 'Дата рождения (дд.мм.гггг):<br /><input name="birthday" maxlength="10" value="'.$udata['users_birthday'].'" /><br />';

	echo 'Аватар: '.user_avatars($log).'<br />';
	echo '<a href="avatars.php">Выбрать</a> или <a href="avatars.php?act=load">Загрузить</a><br />';

	echo 'Пол:<br />';
	echo '<select name="gender">';
	$selected = ($udata['users_gender'] == 1) ? ' selected="selected"' : '';
	echo '<option value="1"'.$selected.'>Мужской</option>';
	$selected = ($udata['users_gender'] == 2) ? ' selected="selected"' : '';
	echo '<option value="2"'.$selected.'>Женский</option>';
	echo '</select><br />';

	echo 'О себе:<br />';
	echo '<textarea id="markItUp" cols="25" rows="5" name="info">'.yes_br($udata['users_info']).'</textarea><br />';

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
	$jabber = (isset($_POST['jabber'])) ? check(strtolower($_POST['jabber'])) : '';
	$site = (isset($_POST['site'])) ? check($_POST['site']) : '';
	$birthday = (isset($_POST['birthday'])) ? check($_POST['birthday']) : '';
	$gender = (isset($_POST['gender'])) ? intval($_POST['gender']) : 0;
	$info = (isset($_POST['info'])) ? check($_POST['info']) : '';

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('regex', array($site, '#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u'), 'Недопустимый адрес сайта, необходим формата http://my_site.domen!', false)
		-> addRule('regex', array($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#'), 'Недопустимый формат даты рождения, необходим формат дд.мм.гггг!', false)
		-> addRule('regex', array($icq, '#^[0-9]{5,10}$#'), 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!', false)
		-> addRule('regex', array($skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#'), 'Недопустимый формат Skype, только латинские символы от 6 до 32!', false)
		-> addRule('regex', array($jabber, '#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#'), 'Недопустимый формат Jabber, необходим формат name@site.domen!', false)
		-> addRule('numeric', $gender, 'Вы не указали ваш пол!', true, 1, 2)
		-> addRule('string', $info, 'Слишком большая информация о себе, не более 1000 символов!',  true, 0, 1000);

	if ($validation->run()) {

		$name = utf_substr($name, 0, 20);
		$country = utf_substr($country, 0, 30);
		$city = utf_substr($city, 0, 50);
		$info = no_br($info);

		DB::run() -> query("UPDATE `users` SET `users_name`=?, `users_country`=?, `users_city`=?, `users_icq`=?, `users_skype`=?, `users_jabber`=?, `users_site`=?, `users_birthday`=?, `users_gender`=?, `users_info`=? WHERE `users_login`=? LIMIT 1;", array($name, $country, $city, $icq, $skype, $jabber, $site, $birthday, $gender, $info, $log));

		notice('Ваш профиль успешно изменен!');
		redirect("profile.php");

	} else {
		show_error($validation->errors);
	}

	echo'<img src="/images/img/back.gif" alt="image" /> <a href="profile.php">Вернуться</a><br />';
break;

default:
	redirect("profile.php");
endswitch;

} else {
	show_login('Вы не авторизованы, чтобы изменять свои данные, необходимо');
}

include_once ('../themes/footer.php');
?>
