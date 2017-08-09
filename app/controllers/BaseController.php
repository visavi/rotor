<?php

Class BaseController
{
    /**
     * Деструктор
     */
    public function __destruct()
    {
        if (isset($_SESSION['input'])) {
            unset($_SESSION['input']);
        }
    }
}
