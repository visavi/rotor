<?php

/**
 * Просмотр ожидающих модерации
 */
case 'waiting':

    //show_title('Список ожидающих модерации файлов');

    echo '<i class="fa fa-book"></i> <a href="/load/add">Публикация</a> / ';
    echo '<b>Ожидающие</b> / ';
    echo '<a href="/load/active">Проверенные</a><hr>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `user`=?;", [0, getUser('login')]);

    if ($total > 0) {
        $querynew = DB::select("SELECT `downs`.*, `name` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? AND `user`=? ORDER BY `time` DESC;", [0, getUser('login')]);

        while ($data = $querynew -> fetch()) {
            echo '<div class="b">';

            echo '<i class="fa fa-download"></i> ';

            echo '<b><a href="/load/add?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.dateFixed($data['time']).')</div>';
            echo '<div>';
            echo 'Категория: '.$data['name'].'<br>';
            if (!empty($data['link'])) {
                echo 'Файл: '.$data['link'].' ('.formatFileSize(UPLOADS.'/files/'.$data['link']).')<br>';
            } else {
                echo 'Файл: <span style="color:#ff0000">Не загружен</span><br>';
            }
            if (!empty($data['screen'])) {
                echo 'Скрин: '.$data['screen'].' ('.formatFileSize(UPLOADS.'/files/'.$data['screen']).')<br>';
            } else {
                echo 'Скрин: <span style="color:#ff0000">Не загружен</span><br>';
            }
            echo '</div>';
        }

        echo '<br>';
    } else {
        showError('Ожидающих модерации файлов еще нет!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add">Вернуться</a><br>';
break;


case 'loadfile':


                        $filename = check(strtolower($_FILES['loadfile']['name']));
                        $isVideo  = strstr($_FILES['loadfile']['type'], "video/") ? true : false;



                                            // Обработка видео
                                            if ($isVideo && env('FFMPEG_ENABLED')) {

                                                $ffconfig = [
                                                    'ffmpeg.binaries'  => env('FFMPEG_PATH'),
                                                    'ffprobe.binaries' => env('FFPROBE_PATH'),
                                                    'timeout'          => 3600,
                                                    'ffmpeg.threads'   => 4,
                                                ];

                                                $ffmpeg = FFMpeg\FFMpeg::create($ffconfig);

                                                $video = $ffmpeg->open(HOME . '/uploads/files/' . $folder . $filename);

                                                // Сохраняем скрин с 5 секунды
                                                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(5));
                                                $frame->save(HOME . '/uploads/screen/' . $folder . '/' . $filename . '.jpg');

                                                if (file_exists(HOME . '/uploads/screen/' . $folder . '/' . $filename . '.jpg')) {
                                                    DB::update("UPDATE `downs` SET `screen`=? WHERE `id`=?;", [$filename . '.jpg', $id]);
                                                }

                                                // Перекодируем видео в h264
                                                $ffprobe = FFMpeg\FFProbe::create($ffconfig);
                                                $codec = $ffprobe
                                                    ->streams(HOME . '/uploads/files/' . $folder . $filename)
                                                    ->videos()
                                                    ->first()
                                                    ->get('codec_name');

                                                if ($ext == 'mp4' && $codec != 'h264') {
                                                    $format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
                                                    $video->save($format, HOME . '/uploads/files/' . $folder . '/convert-' . $filename);
                                                    rename(
                                                        HOME . '/uploads/files/' . $folder . '/convert-' . $filename,
                                                        HOME . '/uploads/files/' . $folder . '/' . $filename
                                                    );
                                                }
                                            }


    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление файла
 */
case 'delfile':

    $link = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($link)) {
        if ($link['user'] == getUser('login')) {
            if (empty($link['active'])) {
                $folder = $link['folder'] ? $link['folder'].'/' : '';

                if (!empty($link['link']) && file_exists(UPLOADS.'/files/'.$folder.$link['link'])) {
                    unlink(UPLOADS.'/files/'.$folder.$link['link']);
                }

                deleteImage('uploads/files/'.$folder, $link['link']);
                deleteImage('uploads/screen/'.$folder, $link['screen']);

                DB::update("UPDATE `downs` SET `link`=?, `screen`=? WHERE `id`=?;", ['', '', $id]);

                setFlash('success', 'Файл успешно удален!');
                redirect("/load/add?act=view&id=$id");

            } else {
                showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            showError('Ошибка! Удаление невозможно, вы не автор данного файла!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление скриншота
 */
case 'delscreen':

    $screen = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($screen)) {
        if ($screen['user'] == getUser('login')) {
            if (empty($screen['active'])) {
                $folder = $screen['folder'] ? $screen['folder'].'/' : '';

                deleteImage('uploads/screen/'.$folder, $screen['screen']);

                DB::update("UPDATE `downs` SET `screen`=? WHERE `id`=?;", ['', $id]);

                setFlash('success', 'Скриншот успешно удален!');
                redirect("/load/add?act=view&id=$id");

            } else {
                showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            showError('Ошибка! Удаление невозможно, вы не автор данного файла!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;
