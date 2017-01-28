<?php

class Guest extends Model {
    public function tweets() {
        return $this->has_many('User');
    }
}
