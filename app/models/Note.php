<?php

class Note extends BaseModel {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'note';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Возвращает связь пользователей
     */
    public function editUser()
    {
        return $this->belongsTo('User', 'edit_user_id');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getEditUser()
    {
        return $this->editUser ? $this->editUser : new User();
    }

    /**
     * Сохраняет заметку для пользоватля
     * @param  object  $note   заметка
     * @param  array   $record массив данных
     * @return integer id затронутой записи
     */
    public static function saveNote($note, array $record)
    {
        if (! $note) {
            $note = new self();
            $note->insert($record);
        } else {
            $note->update($record);
        }

        return $note->id;
    }
}
