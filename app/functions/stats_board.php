<?php

// ------------------- Функция подсчета объявлений --------------------//
function stats_board() {
    $itogoboards = 0;
    if (file_exists(STORAGE."/board/database.dat")) {
        $file = file(STORAGE."/board/database.dat");
        foreach($file as $bval) {
            $dtb = explode("|", $bval);
            if (file_exists(STORAGE."/board/$dtb[2].dat")) {
                $total = counter_string(STORAGE."/board/$dtb[2].dat");
                $itogoboards += $total;
            }
        }
    }
    return (int)$itogoboards;
}
