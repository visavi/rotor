<?php 
#-----------------------------------------------------#
#          ********* ROTORCMS *********               #
#              Made by  :  VANTUZ                     #
#               E-mail  :  visavi.net@mail.ru         #
#                 Site  :  http://pizdec.ru           #
#             WAP-Site  :  http://visavi.net          #
#                  ICQ  :  36-44-66                   #
#  Вы не имеете право вносить изменения в код скрипта #
#        для его дальнейшего распространения          #
#-----------------------------------------------------#	
if (!defined("BASEDIR")) {
    header("Location:../index.php");
    exit;
} 

$msg = utf_lower($msg);
$msg = str_replace(array('настя', 'настенька', 'настюша', 'настюшка'), 'настюха', $msg);

$mssg = "";
$namebots = 'Настюха';
$namebot = utf_lower($namebots);

if ((stristr($msg, $namebot)) and (stristr($msg, 'работа')) or (stristr($msg, $namebot)) and (stristr($msg, 'делаешь')) or (stristr($msg, $namebot)) and (stristr($msg, 'занимаеш'))) {
    $answers = array('Ем!', 'Музыку слушаю...', 'Телевизор смотрю...', 'Да так... ничем, а ты?', 'Туплю понемногу');
    $answernumber = array_rand($answers);
    $mssg = $answers[$answernumber];
} 

if ($mssg == "") {
    if ((stristr($msg, 'всем привет')) or (stristr($msg, 'здравствуйте')) or (stristr($msg, 'всем хай')) or (stristr($msg, 'всем здарова')) or (stristr($msg, 'хочу общения')) or (stristr($msg, 'класс'))) {
        $answers = array('как дела?', 'как жизнь?', 'хай!', 'давай пообщаемся');
        $answernumber = array_rand($answers);
        $mssg = $newname . ', ' . $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if (((stristr($msg, $namebot)) and (stristr($msg, 'привет'))) or (stristr($msg, $namebot)) and (stristr($msg, 'приветик')) or (stristr($msg, $namebot)) and (stristr($msg, 'с возвращением')) or (stristr($msg, 'всем привет')) or (stristr($msg, 'привет всем'))) {
        $answers = array('Здравствуй!', 'Приветик  ', 'Ооо привет =)!', 'Угумс  Тебе тоже.', 'Дарова =)');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'как')) and (stristr($msg, 'дела')) or (stristr($msg, $namebot)) and (stristr($msg, 'как')) and (stristr($msg, 'оно')) or (stristr($msg, $namebot)) and (stristr($msg, 'как')) and (stristr($msg, 'настроение')) or (stristr($msg, $namebot)) and (stristr($msg, 'как')) and (stristr($msg, 'дели'))) {
        $answers = array('Хорошо!', 'Плохо', 'Спасибо, но бывало лучше', 'Отлично! У тебя как?', 'Терпимо');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'скучно'))) {
        $answers = array('а ты попрыгай', 'Мне тоже...');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'работа')) or (stristr($msg, $namebot)) and (stristr($msg, 'делаешь')) or (stristr($msg, $namebot)) and (stristr($msg, 'занимаеш'))) {
        $answers = array('Ем!', 'Музыку слушаю...', 'Телевизор смотрю...', 'Да так... ничем, а ты?', 'Туплю понемногу');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'откуда')) or (stristr($msg, $namebot)) and (stristr($msg, 'живешь'))) {
        $answers = array('Я с Оренбурга', 'От верблюда', 'Откуда, откуда, что так интерестно', 'Не скажу', 'Оттуда где встает солнце');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'интерес')) or (stristr($msg, $namebot)) and (stristr($msg, 'любопыт'))) {
        $answers = array('Меньше знаешь - лучше спишь', 'Спроси у Киндера он много знает', 'Любопытной варваре на базаре нос оторвали', 'Интерестно когда в бане тесно');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'ум'))) {
        $mssg = 'Да я очень умная!';
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'как')) and (stristr($msg, 'зовут'))) {
        $answers = array('Написано же, Настюха!', 'Секрет...', 'Лена  ', 'Ангелина', 'Меня не зовут, я сама прихожу... и всегда вовремя..');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'сколько')) and (stristr($msg, 'лет'))) {
        $answers = array('А сколько надо?', 'Пусть это останется моим небольшим секретом', '18, а тебе?', 'Сначала ты  ', 'Неприличный вопрос для девушки...');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'пока')) or (stristr($msg, $namebot)) and (stristr($msg, 'бывай')) or (stristr($msg, $namebot)) and (stristr($msg, 'удачи'))) {
        $answers = array('Эээх уже уходишь? Ну ладно... заползай ежели что!', 'Пока пока...', 'Давай... удачи тебе!', 'Ээх и на кого ты меня бросаешь.. ', 'Давай уже!');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, 'всем')) and (stristr($msg, 'пока')) or (stristr($msg, 'всем')) and (stristr($msg, 'счастл')) or (stristr($msg, 'всем')) and (stristr($msg, 'удачи'))) {
        $answers = array('Эээх уже уходишь? Ну ладно... заползай ежели что!', 'Пока пока...', 'Давай... удачи тебе!', 'Ээх и на кого ты меня бросаешь.. ', 'Давай уже!');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'спасибо'))) {
        $answers = array('Да не за что...', 'Спасибо в карман не положишь...', 'Пожалуйста!', 'Рада помочь.', 'Всегда рада помочь..');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'знаком'))) {
        $answers = array('Надо подумать...', 'Давай, меня зовут Настюха, а тебя?', 'Нехочу =)');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'хоче'))) {
        $answers = array('Хочу!', 'Да!', 'Неа!', 'Нет, спасибо...', 'Не знаю...');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'будешь'))) {
        $answers = array('С удовольствием...', 'Да!', 'Неа!', 'Нет, спасибо...', 'Не знаю...');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'дура')) or (stristr($msg, $namebot)) and (stristr($msg, 'тупая'))) {
        $answers = array('Не хами!', 'Сам такой', 'ты в игноре');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'почему')) or (stristr($msg, $namebot)) and (stristr($msg, 'из-за чего'))) {
        $answers = array('Лучше не спрашивай!', 'так получилось...', 'не хочу об этом говорить... =(');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, 'www.')) or (stristr($msg, '.ru')) or (stristr($msg, '.net')) or (stristr($msg, 'wap.')) or (stristr($msg, '.com')) or (stristr($msg, 'chat')) or (stristr($msg, '.spb')) or (stristr($msg, 'http://')) or (stristr($msg, '.msk'))) {
        $answers = array($log . ', за рекламу получишь бан', $log . ',  кто-то щас пострадает', $log . ', для этого есть раздел афиша');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, 'хуй')) or (stristr($msg, 'сук')) or (stristr($msg, 'сучк')) or (stristr($msg, 'бля')) or (stristr($msg, 'пизд')) or (stristr($msg, '***'))) {
        $answers = array('Бан хочешь? Могу устроить!', 'А в глаз?', 'Администрация чата делает Вам предупреждение, за нарушение правил!', 'Не буди во мне зверя...');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'люблю')) or (stristr($msg, $namebot)) and (stristr($msg, 'обним')) or (stristr($msg, $namebot)) and (stristr($msg, 'целую')) or (stristr($msg, $namebot)) and (stristr($msg, 'скучаю'))) {
        $answers = array('Нежно обнимаю и притягиваю к себе', 'Крепко по-дружески обнимаю', 'Ты мне не интересен', 'Может вначале узнаем друг друга');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'кто'))) {
        $answers = array('Конь в пальто', 'Пушкин', 'Что так интерестно', 'Не твое дело', 'Не скажу');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'что'))) {
        $answers = array('Догадайся', 'Что-то что не нужно тебе знать', 'Через плечо', 'Не твое дело', 'Не скажу');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'торт'))) {
        $mssg = 'Сам жри эту гадость, я не люблю сладкое';
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'цветы'))) {
        $mssg = 'Ой, спасибо! Мне очень нравяться цветы';
    } 
} 

if ($mssg == "") {
    if ((stristr($msg, $namebot)) and (stristr($msg, 'бот'))) {
        $answers = array('Cам ты бот придурок', 'Если я бот, то ты урод', 'Ща кто-то в баню отправится', 'Тебе не кажется лучше, что меня не злить', 'Я не бот, очень симпатичная девчонка');
        $answernumber = array_rand($answers);
        $mssg = $answers[$answernumber];
    } 
} 

?>