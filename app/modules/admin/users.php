<?php

            $uid = check($_GET['uid']);
            $loginblack = (empty($_POST['loginblack'])) ? 0 : 1;
            $mailblack = (empty($_POST['mailblack'])) ? 0 : 1;
            $deltopicforum = (empty($_POST['deltopicforum'])) ? 0 : 1;
            $delpostforum = (empty($_POST['delpostforum'])) ? 0 : 1;
            $delcommphoto = (empty($_POST['delcommphoto'])) ? 0 : 1;
            $delcommnews = (empty($_POST['delcommnews'])) ? 0 : 1;
            $delcommblog = (empty($_POST['delcommblog'])) ? 0 : 1;
            $delcommload = (empty($_POST['delcommload'])) ? 0 : 1;
            $delimages = (empty($_POST['delimages'])) ? 0 : 1;



                        // ------ Удаление тем в форуме -------//
                        if (!empty($delpostforum) || !empty($deltopicforum)) {

                            $query = DB::select("SELECT `id` FROM `topics` WHERE `author`=?;", [$uz]);
                            $topics = $query -> fetchAll(PDO::FETCH_COLUMN);

                            if (!empty($topics)){
                                $strtopics = implode(',', $topics);

                                // ------ Удаление загруженных файлов -------//
                            /*    foreach($topics as $delDir){
                                    removeDir(UPLOADS.'/forum/'.$delDir);
                                }*/
                                DB::delete("DELETE FROM `files_forum` WHERE `post_id` IN (".$strtopics.");");
                                // ------ Удаление загруженных файлов -------//

                                DB::delete("DELETE FROM `posts` WHERE `topic_id` IN (".$strtopics.");");
                                DB::delete("DELETE FROM `topics` WHERE `id` IN (".$strtopics.");");
                            }

                            // ------ Удаление сообщений в форуме -------//
                            if (!empty($delpostforum)) {
                                DB::delete("DELETE FROM `posts` WHERE `user`=?;", [$uz]);

                                // ------ Удаление загруженных файлов -------//
                                $queryfiles = DB::select("SELECT * FROM `files_forum` WHERE `user`=?;", [$uz]);
                                $files = $queryfiles->fetchAll();

                                if (!empty($files)){
                                    foreach ($files as $file){
                                        if (file_exists(UPLOADS.'/forum/'.$file['topic_id'].'/'.$file['hash'])){
                                            unlink(UPLOADS.'/forum/'.$file['topic_id'].'/'.$file['hash']);
                                        }
                                    }
                                    DB::delete("DELETE FROM `files_forum` WHERE `user`=?;", [$uz]);
                                }
                                // ------ Удаление загруженных файлов -------//
                            }

                            restatement('forum');
                        }






