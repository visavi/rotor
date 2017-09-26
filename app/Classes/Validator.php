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

    /**
     * Проверяет длину строки
     *
     * @param  string $input
     * @param  int    $min
     * @param  int    $max
     * @param  mixed  $label
     * @param  bool   $required
     * @return $this
     */
    public function length($input, $min, $max, $label, $required = true)
    {
        if ($required === false && mb_strlen($input, 'utf-8') === 0) {
            return $this;
        }

        if (mb_strlen($input, 'utf-8') < $min) {
            $this->addError($label, ' (Не менее '.$min.' симв.)');
        } elseif (mb_strlen($input, 'utf-8') > $max) {
            $this->addError($label, ' (Не более '.$max.' симв.)');
        }

        return $this;
    }

    /**
     * Проверяет число на вхождение в диапазон
     *
     * @param  int   $input
     * @param  int   $min
     * @param  int   $max
     * @param  mixed $label
     * @param  bool  $required
     * @return $this
     */
    public function between($input, $min, $max, $label, $required = true)
    {
        if ($required === false && $input === 0) {
            return $this;
        }

        if ($input < $min || $input > $max) {
            $this->addError($label, ' (Между '.$min.' и '.$max.')');
        }

        return $this;
    }

    /**
     * Проверяет на больше чем число
     *
     * @param  int   $input
     * @param  int   $input2
     * @param  mixed $label
     * @return $this
     */
    public function greaterThan($input, $input2, $label)
    {
        if ($input <= $input2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на больше чем или равно
     *
     * @param  int   $input
     * @param  int   $input2
     * @param  mixed $label
     * @return $this
     */
    public function greaterThanOrEqual($input, $input2, $label)
    {
        if ($input < $input2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на меньше чем число
     *
     * @param  int   $input
     * @param  int   $input2
     * @param  mixed $label
     * @return $this
     */
    public function lessThan($input, $input2, $label)
    {
        if ($input >= $input2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на меньше чем или равно
     *
     * @param  int   $input
     * @param  int   $input2
     * @param  mixed $label
     * @return $this
     */
    public function lessThanOrEqual($input, $input2, $label)
    {
        if ($input > $input2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет эквивалентны ли данные
     *
     * @param  mixed $input
     * @param  mixed $input2
     * @param  mixed $label
     * @return $this
     */
    public function equal($input, $input2, $label)
    {
        if ($input !== $input2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет не эквивалентны ли данные
     *
     * @param  mixed $input
     * @param  mixed $input2
     * @param  mixed $label
     * @return $this
     */
    public function notEqual($input, $input2, $label)
    {
        if ($input === $input2) {
            $this->addError($label);
        }

        return $this;
    }

    public function bool($input, $label)
    {
        if (filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
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
