<?php
/**
 * class FileUpload
 * Дополнительно реализовано, расширеные подсказки в ошибках
 * Возможность загрузки файлов, только определенных расширений
 */
class FileUpload extends upload {

    function translate($str, $tokens = array()) {

        // sets default language
        $this->translation = array();
        $this->translation['file_error']                  = 'Файловая ошибка. Попробуйте еще раз.';
        $this->translation['local_file_missing']          = 'Локальный файл не существует.';
        $this->translation['local_file_not_readable']     = 'Локальный файл закрыт для чтения.';
        $this->translation['uploaded_too_big_ini']        = 'Ошибка! Загруженный файл превышает лимит директивы the upload_max_filesize';
        $this->translation['uploaded_too_big_html']       = 'Ошибка! Загруженный файл превышает лимит директивы MAX_FILE_SIZE';
        $this->translation['uploaded_partial']            = 'Ошибка загрузки файла (Файл загружен частично).';
        $this->translation['uploaded_missing']            = 'Ошибка загрузки файла (Файл не был загружен).';
        $this->translation['uploaded_no_tmp_dir']         = 'Ошибка загрузки файла (Временная папка не найдена).';
        $this->translation['uploaded_cant_write']         = 'Ошибка загрузки файла (Не удалось записать файл на диск).';
        $this->translation['uploaded_err_extension']      = 'Ошибка загрузки файла (Не удалось определить расширение).';
        $this->translation['uploaded_unknown']            = 'Ошибка загрузки файла (Неизвестный код ошибки).';
        $this->translation['try_again']                   = 'Ошибка загрузки файла. Попробуйте еще раз.';
        $this->translation['file_too_big']                = 'Ошибка! Слишком большой вес файла! Максимум %s';
        $this->translation['no_mime']                     = 'Невозможно определить MIME-тип файла.';
        $this->translation['incorrect_file']              = 'Ошибка! Некорректный тип файла.';
        $this->translation['incorrect_ext']               = 'Ошибка! Недопустимое расширение файла. Разрешено: %s';
        $this->translation['image_too_wide']              = 'Ошибка! Размер изображения очень широкий. Максимум %s px';
        $this->translation['image_too_narrow']            = 'Ошибка! Размер изображения очень узкий. Минимум %s px';
        $this->translation['image_too_high']              = 'Ошибка! Размер изображения очень высокий. Максимум %s px';
        $this->translation['image_too_short']             = 'Ошибка! Размер изображения очень короткий. Минимум %s px';
        $this->translation['ratio_too_high']              = 'Соотношение сторон очень велико (Изображение очень широкое).';
        $this->translation['ratio_too_low']               = 'Соотношение сторон очень мало (Изображение очень высокое).';
        $this->translation['too_many_pixels']             = 'В изображении очень много пикселей.';
        $this->translation['not_enough_pixels']           = 'В изображении недостаточно пикселей.';
        $this->translation['file_not_uploaded']           = 'Файл не загружен. Невозможно продолжить процесс.';
        $this->translation['already_exists']              = '%s существует. Измените имя файла.';
        $this->translation['temp_file_missing']           = 'Некорректный временый файл. Невозможно продолжить процесс.';
        $this->translation['source_missing']              = 'Некорректный загруженный файл. Невозможно продолжить процесс.';
        $this->translation['destination_dir']             = 'Директория назначения не может быть создана. Невозможно продолжить процесс.';
        $this->translation['destination_dir_missing']     = 'Директория назначения не существует. Невозможно продолжить процесс.';
        $this->translation['destination_path_not_dir']    = 'Путь назначения не является директорией. Невозможно продолжить процесс.';
        $this->translation['destination_dir_write']       = 'Директория назначения закрыта для записи. Невозможно продолжить процесс.';
        $this->translation['destination_path_write']      = 'Путь назначения закрыт для записи. Невозможно продолжить процесс.';
        $this->translation['temp_file']                   = 'Невозможно создать временный файл. Невозможно продолжить процесс.';
        $this->translation['source_not_readable']         = 'Исходный файл нечитабельный. Невозможно продолжить процесс.';
        $this->translation['no_create_support']           = 'Создание из %s не поддерживается.';
        $this->translation['create_error']                = 'Ошибка создания %s изображения из оригинала.';
        $this->translation['source_invalid']              = 'Невозможно прочитать исходный файл.';
        $this->translation['gd_missing']                  = 'Библиотека GD не обнаружена.';
        $this->translation['watermark_no_create_support'] = '%s не поддерживается, невозможно прочесть водный знак.';
        $this->translation['watermark_create_error']      = '%s не поддерживается чтение, невозможно создать водный знак.';
        $this->translation['watermark_invalid']           = 'Неизвестный формат изображения, невозможно прочесть водный знак.';
        $this->translation['file_create']                 = '%s не поддерживается.';
        $this->translation['no_conversion_type']          = 'Тип конверсии не указан.';
        $this->translation['copy_failed']                 = 'Ошибка копирования файла на сервер. Команда copy() выполнена с ошибкой.';
        $this->translation['reading_failed']              = 'Ошибка чтения файла.';

        if (array_key_exists($str, $this->translation)) $str = $this->translation[$str];
        if (is_array($tokens) && sizeof($tokens) > 0)   $str = vsprintf($str, $tokens);
        return $str;
    }

    function init() {
        parent::init(); // Array list of allowed extensions
        $this->ext_check = array();
    }

    function process($server_path = null) {
        $this->error     = '';
        $this->processed = true;

        if (!$this->uploaded) {
            $this->error = $this->translate('file_not_uploaded');
            $this->processed = false;
        }

        if ($this->processed) {
            if (!empty($this->ext_check) && !in_array($this->file_src_name_ext, $this->ext_check)) {
                $this->processed = false;
                $this->error = $this->translate('incorrect_ext', array(implode(', ', $this->ext_check)));
            } else {
                $this->log .= '- file ext OK<br />';
            }
        }

        if ($this->processed) {
            // checks file max size
            if ($this->file_src_size > $this->file_max_size) {
                $this->processed = false;
                $this->error = $this->translate('file_too_big', array(formatsize($this->file_max_size)));
            } else {
                $this->log .= '- file size OK<br />';
            }
        }

        if ($this->processed) {
            if ($this->file_is_image) {
                if (is_numeric($this->image_src_x) && is_numeric($this->image_src_y)) {

                    if (!is_null($this->image_max_width) && $this->image_src_x > $this->image_max_width) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_wide', array($this->image_max_width));
                    }
                    if (!is_null($this->image_min_width) && $this->image_src_x < $this->image_min_width) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_narrow', array($this->image_min_width));
                    }
                    if (!is_null($this->image_max_height) && $this->image_src_y > $this->image_max_height) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_high', array($this->image_max_height));
                    }
                    if (!is_null($this->image_min_height) && $this->image_src_y < $this->image_min_height) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_short', array($this->image_min_height));
                    }
                } else {
                    $this->log .= '- no image properties available, can\'t enforce dimension checks : ' . $this->file_src_mime . '<br />';
                }
            }
        }

        if ($this->processed) {
            parent::process($server_path);
        }
    }
}

?>
