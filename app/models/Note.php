<?php

class Note extends BaseModel {

    /**
     * Сохраняет заметку для пользоватля
     * @param  object  $note   заметка
     * @param  array   $record массив данных
     * @return integer id затронутой записи
     */
    public static function saveNote($note, array $record)
    {
        if (! $note) {
            $note = self::create();
        }
        $note->set($record);
        $note->save();

        return $note->id;
    }
}
