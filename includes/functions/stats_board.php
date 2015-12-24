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

// ------------------- Функция подсчета объявлений --------------------//
function stats_board() {
    $itogoboards = 0;
    if (file_exists(DATADIR."/board/database.dat")) {
        $file = file(DATADIR."/board/database.dat");
        foreach($file as $bval) {
            $dtb = explode("|", $bval);
            if (file_exists(DATADIR."/board/$dtb[2].dat")) {
                $total = counter_string(DATADIR."/board/$dtb[2].dat");
                $itogoboards += $total;
            }
        }
    }
    return (int)$itogoboards;
}
?>
