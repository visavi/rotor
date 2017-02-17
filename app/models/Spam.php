<?php

class Spam extends BaseModel {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spam';

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

}
