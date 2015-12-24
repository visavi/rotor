<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
if (!defined('BASEDIR')) {
    header("Location:../index.php");
    exit;
}
// ------------------------ Функция записи в файл ------------------------//
if (!function_exists('write_files')){
function write_files($filename, $text, $clear = 0, $chmod = "") {
    $fp = fopen($filename, "a+");
    flock ($fp, LOCK_EX);
    if ($clear == 1) {
        ftruncate($fp, 0);
    }
    fputs ($fp, $text);
    fflush($fp);
    flock ($fp, LOCK_UN);
    fclose($fp);
    if ($chmod != "") {
        chmod($filename, $chmod);
    }
}}
// ------------------- Функция подсчета строк в файле--------------------//

if (!function_exists('counter_string')){
function counter_string($files) {
    $count_lines = 0;
    if (file_exists($files)) {
        $lines = file($files);
        $count_lines = count($lines);
    }
    return $count_lines;
}}
// ------------------- Функция удаления строк(и) из файла --------------------//
if (!function_exists('delete_lines')){
function delete_lines($files, $lines) {
    if ($lines !== "") {
        if (file_exists($files)) {
            if (!is_array($lines)) {
                $file = file($files);
                $fp = fopen($files, "a+");
                flock ($fp, LOCK_EX);
                ftruncate ($fp, 0);
                if (isset($file[$lines])) {
                    unset($file[$lines]);
                }
                fputs ($fp, implode($file));
                fflush($fp);
                flock ($fp, LOCK_UN);
                fclose($fp);
                unset ($lines);
            } else {
                $file = file($files);
                $fp = fopen($files, "a+");
                flock ($fp, LOCK_EX);
                ftruncate ($fp, 0);
                foreach($lines as $val) {
                    if (isset($file[$val])) {
                        unset($file[$val]);
                    }
                }
                fputs ($fp, implode($file));
                fflush($fp);
                flock ($fp, LOCK_UN);
                fclose($fp);
                unset ($lines);
            }
        }
    }
}}


// ------------------- Функция очистки файла --------------------//
if (!function_exists('clear_files')){
function clear_files($files) {
    if (file_exists($files)) {
        $file = file($files);
        $fp = fopen($files, "a+");
        flock ($fp, LOCK_EX);
        ftruncate ($fp, 0);
        fflush($fp);
        flock ($fp, LOCK_UN);
        fclose($fp);
    }
}}

// ------------------- Функция замены строки в файлe --------------------//
if (!function_exists('replace_lines')){
function replace_lines($files, $lines, $text) {
    if (file_exists($files)) {
        if ($lines !== "") {
            if ($text != "") {
                $file = file($files);
                $fp = fopen($files, "a+");
                flock ($fp, LOCK_EX);
                ftruncate ($fp, 0);

                foreach($file as $key => $val) {
                    if ($lines == $key) {
                        fputs($fp, "$text\r\n");
                    } else {
                        fputs($fp, $val);
                    }
                }

                fflush($fp);
                flock ($fp, LOCK_UN);
                fclose($fp);
            }
        }
    }
}}

// ------------------ Функция проверки ячейки строки в файле ------------------//
if (!function_exists('search_string')){
function search_string($file, $str, $ceil) {
    if (file_exists($file)) {
        $files = file($file);

        foreach($files as $key => $value) {
            $data = explode("|", $value);

            if ($data[$ceil] == $str) {
                $data['line'] = $key;
                return $data;
                break;
            }
        }
    }

    return false;
}}

// ----------------------- Функция уникального ID по данным из файла ------------------------//
if (!function_exists('unifile')){
function unifile($file, $ceil) {
    if (file_exists($file)) {
        if ($ceil !== "") {
            $arrdata = array(0);

            $files = file($file);
            foreach($files as $value) {
                $data = explode("|", $value);

                if (isset($data[$ceil])) {
                    $arrdata[] = (int)$data[$ceil];
                }
            }

            return max($arrdata) + 1;
        }
    }

    return 1;
}}


// ------------------- Функция сдига строки в файле --------------------//
if (!function_exists('move_lines')){
function move_lines($files, $lines, $where) {
    if (file_exists($files)) {
        if ($lines !== "") {
            if ($where !== "") {
                if ($where == 1) {
                    $lines2 = $lines + 1;
                } else {
                    $lines2 = $lines - 1;
                }

                $file = file($files);

                if (isset($file[$lines]) && isset($file[$lines2])) {
                    $fp = fopen($files, "a+");
                    flock ($fp, LOCK_EX);
                    ftruncate ($fp, 0);

                    foreach($file as $key => $val) {
                        if ($lines == $key) {
                            fputs($fp, $file[$lines2]);
                        } elseif ($lines2 == $key) {
                            fputs($fp, $file[$lines]);
                        } else {
                            fputs($fp, $val);
                        }
                    }

                    fflush($fp);
                    flock ($fp, LOCK_UN);
                    fclose($fp);
                }
            }
        }
    }
}}
?>
