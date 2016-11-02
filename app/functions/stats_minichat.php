<?php

// ------------------- Функция подсчета объявлений --------------------//
function stats_minichat() {
    if (file_exists(STORAGE."/chat/chat.dat")) {

        $files = file(STORAGE."/chat/chat.dat");
        $data = explode("|", end($files));

        if (isset($data[9])) {
          return (int)$data[9];
        }
    }

    return 0;
}
