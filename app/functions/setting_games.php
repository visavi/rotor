<?php

$config['vkladlist'] = 10;			// Число вкладчиков на странице

$config['jackpot'] = 10000;			// Сумма выигрыша в лотереи
$config['ochkostavka'] = 1000;		// Максимальная сумма ставки в игре 21 очко

$config['hiprize'] = 100;     		// Сумма выигрыша в игре Угадай число
$config['hipopytka'] = 5;			// Количество попыток
$config['hisumma'] = 5;				// Цена за попытку угадать число

$config['maxsumbank'] = 1000000;	// Максимальная сумма хранения в банке

$config['minkredit'] = 1000;		// Минимальная сумма кредита
$config['maxkredit'] = 100000;		// Максимальная сумма кредита
$config['percentkredit'] = 10;		// Процента при максимальной сумме
$config['creditpoint']	= 150;		// Количество баллов для взятия кредита

$config['safesum'] = 1000;			// Всего денег в сейфе
$config['safeattempt'] = 100;		// Цена попытки взлома сейфа

// --------------- Функция подсчета карт в игре ---------------//
function cards_score($str) {
    if ($str > 32) return 11;
    if ($str > 20) return (int)(($str-1) / 4)-3;
    return (int)(($str-1) / 4) + 6;
}

// --------------- Функция подсчета очков в игре ---------------//
function cards_points($str) {
    $str = (int)$str;

    $str1 = abs($str) % 100;
    $str2 = $str % 10;

    if ($str1 == 21) return $str.' <b>очко!!!</b>';
    if ($str1 > 10 && $str1 < 20) return $str.' очков';
    if ($str2 > 1 && $str2 < 5) return $str.' очка';
    if ($str2 == 1) return $str.' очко';

    return $str.' очков';
}

// ------------------- Функция подсчета процента в банке --------------------//
function percent_bank($money) {
    switch ($money) {
        case ($money >= 5000000): $stavka = 0.5;
            break;
        case ($money >= 1000000): $stavka = 1;
            break;
        case ($money >= 500000): $stavka = 2;
            break;
        case ($money >= 250000): $stavka = 3;
            break;
        case ($money >= 100000): $stavka = 6;
            break;
        default: $stavka = 10;
    }

    return round(($money * $stavka) / 100);
}

// --------------- Функция сохранения количества денег в банке ---------------//
function save_bankmoney($time = 0) {
    if (empty($time) || @filemtime(STORAGE."/temp/moneybank.dat") < time() - $time) {
        $querybank = DB::run() -> query("SELECT `bank_user`, `bank_sum` FROM `bank` WHERE `bank_sum`>?;", array(0));
        $allbank = $querybank -> fetchAssoc();
        file_put_contents(STORAGE."/temp/moneybank.dat", serialize($allbank), LOCK_EX);
    }
}

// --------------- Функция подсчета денег в банке ---------------//
function user_bankmoney($login) {
    static $arrbank;

    if (empty($arrbank)) {
        save_bankmoney(3600);
        $arrbank = unserialize(file_get_contents(STORAGE."/temp/moneybank.dat"));
    }

    return (isset($arrbank[$login])) ? $arrbank[$login] : 0;
}
