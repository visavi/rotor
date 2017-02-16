<?php

class FileForum extends BaseModel {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files_forum';

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Disable updated_at
     */
    public function setUpdatedAtAttribute($value)
    {
        // to Disable updated_at
    }
}
