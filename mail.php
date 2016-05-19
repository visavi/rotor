<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('includes/start.php');
require_once ('includes/functions.php');
require_once ('includes/header.php');
include_once ('themes/header.php');


/**
 * Отправка уведомления на email
 * @param  mixed   $to      Получатель
 * @param  string  $subject Тема письма
 * @param  string  $body    Текст сообщения
 * @param  array   $headers Дополнительные параметры
 * @return boolean  Результат отправки
 */
function sendMail($to, $subject, $body, $headers = [])
{
$maildriver = 'smtp';
	if (empty($headers['from'])) $headers['from'] = ['admin@visavi.net' => 'Vantuz'];

	$message = Swift_Message::newInstance()
		->setTo($to)
		->setSubject($subject)
		->setBody($body, 'text/html')
		->setFrom($headers['from'])
		->setReturnPath('admin@visavi.net');

	if ($maildriver == 'smtp') {
		$transport = Swift_SmtpTransport::newInstance('smtp.yandex.ru', 25, 'ssl')
			->setUsername('admin@visavi.net')
			->setPassword('');
	} else {
		$transport = new Swift_MailTransport();
	}

	$mailer = new Swift_Mailer($transport);
	return $mailer->send($message);
}



var_dump(sendMail('visavi.net@mail.ru', 'Привет', 'Я порву тебя'));

include_once ('themes/footer.php');
?>
