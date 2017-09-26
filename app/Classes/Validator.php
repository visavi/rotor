<?php

namespace App\Classes;

/**
 * Класс валидации данных
 * Выполняет простейшую валидацию данных, длина строк, размеры чисел, сравнение, наличие в списке итд
 *
 * @license Code and contributions have MIT License
 * @link    http://visavi.net
 * @author  Alexander Grigorev <admin@visavi.net>
 */
class Validator
{
    /**
     * @var array validation errors
     */
    private $errors = [];

    public function length($var, $min, $max = null, $label, $required = true)
    {
        if ($required == false && mb_strlen($var, 'utf-8') == 0) {
            return $this;
        }

        if (mb_strlen($var, 'utf-8') < $min) {
            $this->addError($label, ' (Не менее ' . $min . ' симв.)');
        } elseif (mb_strlen($var, 'utf-8') > $max) {
            $this->addError($label, ' (Не более ' . $max . ' симв.)');
        }

        return $this;
    }


    /**
     * Проверяет на больше чем число
     *
     * @param  int   $var
     * @param  int   $var2
     * @param  mixed $label
     * @return $this
     */
    public function greaterThan($var, $var2, $label)
    {
        if ($var <= $var2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на больше чем или равно
     *
     * @param  int   $var
     * @param  int   $var2
     * @param  mixed $label
     * @return $this
     */
    public function greaterThanOrEqual($var, $var2, $label)
    {
        if ($var < $var2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на меньше чем число
     *
     * @param  int   $var
     * @param  int   $var2
     * @param  mixed $label
     * @return $this
     */
    public function lessThan($var, $var2, $label)
    {
        if ($var >= $var2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на меньше чем или равно
     *
     * @param  int   $var
     * @param  int   $var2
     * @param  mixed $label
     * @return $this
     */
    public function lessThanOrEqual($var, $var2, $label)
    {
        if ($var > $var2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет эквивалентны ли данные
     *
     * @param  mixed $var
     * @param  mixed $var2
     * @param  mixed $label
     * @return $this
     */
    public function equal($var, $var2, $label)
    {
        if ($var !== $var2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Добавляет ошибки в массив
     *
     * @param mixed  $error       текст ошибки
     * @param string $description
     */
    public function addError($error, $description = null)
    {
        $key = 0;

        if (is_array($error)) {
            $key   = key($error);
            $error = current($error);
        }

        if (isset($this->errors[$key])) {
            $this->errors[] = $error.$description;
        } else {
            $this->errors[$key] = $error.$description;
        }
    }

    /**
     * Возвращает список ошибок
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Возвращает успешность валидации
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }
}
