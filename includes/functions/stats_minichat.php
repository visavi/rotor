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
if (!defined('BASEDIR')) {
  header("Location:../index.php");
  exit;
}
// ------------------- Функция подсчета объявлений --------------------//
function stats_minichat() {
  if (file_exists(DATADIR."/temp/chat.dat")) {

    $files = file(DATADIR."/temp/chat.dat");
    $data = explode("|", end($files));

    if (isset($data[9])) {
      return (int)$data[9];
    }
  }

  return 0;
}

?>
