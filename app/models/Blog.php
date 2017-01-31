<?php

class Blog extends BaseModel {

    public static $_table = 'blogs';

    /**
     * Возвращает последнии комментарии
     * @return \Granada\ORM|null
     */
    public function lastComments($limit = 15)
    {
        return $this->has_many('Comment', 'relate_id')->where('relate_type', 'blog')->limit($limit);
    }
}
