<?php
/**
 * Класс валидации данных
 * Выполняет простейшую валидацию данных, длина строк, размеры чисел, сравнение, наличие в списке итд
 * @license Code and contributions have MIT License
 * @link    http://visavi.net
 * @author  Alexander Grigorev <visavi.net@mail.ru>
 */
class Validation
{
    /**
     * @var array validation errors
     */
    private $errors = [];

    /**
     * @var array validation rules
     */
    private $validation_rules = [];

    /**
     * the constructor, duh!
     */
    public function __construct()
    {
    }

    /**
     * run the validation rules
     * @return bool;
     */
    public function run()
    {
        foreach (new ArrayIterator($this->validation_rules) as $opt) {
            switch ($opt['type']) {
                case 'string':
                    $this->validateString($opt['var'], $opt['label'], $opt['min'], $opt['max'], $opt['required']);
                    break;

                case 'numeric':
                    $this->validateNumeric($opt['var'], $opt['label'], $opt['min'], $opt['max'], $opt['required']);
                    break;

                case 'max':
                    $this->validateMax($opt['var'], $opt['label']);
                    break;

                case 'min':
                    $this->validateMin($opt['var'], $opt['label']);
                    break;

                case 'equal':
                    $this->validateEqual($opt['var'], $opt['label']);
                    break;

                case 'not_equal':
                    $this->validateNotEqual($opt['var'], $opt['label']);
                    break;

                case 'empty':
                    $this->validateEmpty($opt['var'], $opt['label']);
                    break;

                case 'not_empty':
                    $this->validateNotEmpty($opt['var'], $opt['label']);
                    break;

                case 'in':
                    $this->validateIn($opt['var'], $opt['label']);
                    break;

                case 'regex':
                    $this->validateRegex($opt['var'], $opt['label'], $opt['required']);
                    break;

                case 'float':
                    $this->validateFloat($opt['var'], $opt['label'], $opt['required']);
                    break;

                case 'url':
                    $this->validateUrl($opt['var'], $opt['label'], $opt['required']);
                    break;

                case 'email':
                    $this->validateEmail($opt['var'], $opt['label'], $opt['required']);
                    break;

                case 'bool':
                    $this->validateBool($opt['var'], $opt['label']);
                    break;

                case 'custom':
                    $this->validateCustom($opt['var'], $opt['label']);
                    break;

                default:
                    $this->addError('Ошибка! Не найден тип правила "' . $opt['type'] . '"');
            }
        }

        if ($this->getErrors()) {
            return false;
        }

        return true;
    }

    /**
     * add a rule to the validation rules array
     * @param string $type The type of variable
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param bool $required If the field is required
     * @param int $min The minimum length or range
     * @param int $max The maximum length or range
     */
    public function addRule($type, $var, $label, $required = false, $min = 0, $max = 0)
    {
        $this->validation_rules[] = compact('type', 'var', 'label', 'required', 'min', 'max');
        return $this;
    }

    /**
     * displays an error
     * @param string $error The error text
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
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * validate a string
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param int $min The minimum string length
     * @param int $max The maximum string length
     * @param bool $required
     */
    private function validateString($var, $label, $min = 0, $max = 0, $required = false)
    {
        if ($required == false && mb_strlen($var, 'utf-8') == 0) {
            return true;
        }

        if (mb_strlen($var, 'utf-8') < $min) {
            $this->addError($label, ' (Не менее ' . $min . ' симв.)');
        } elseif (mb_strlen($var, 'utf-8') > $max) {
            $this->addError($label, ' (Не более ' . $max . ' симв.)');
        }
    }

    /**
     * Checks whether numeric input has a minimum value
     * @param  array $var   The variable
     * @param  mixed $label The label of variable
     * @return bool
     */
    private function validateMin($var, $label)
    {
        if (is_array($var) && count($var) == 2 && $var[0] <= $var[1]) {
            return true;
        } else {
            $this->addError($label);
        }
    }

    /**
     * Checks whether numeric input has a maximum value
     * @param  array $var   The variable
     * @param  mixed $label The label of variable
     * @return bool
     */
    private function validateMax($var, $label)
    {
        if (is_array($var) && count($var) == 2 && $var[0] >= $var[1]) {
            return true;
        } else {
            $this->addError($label);
        }
    }

    /**
     * validate an number
     * @param int $var The variable
     * @param mixed $label The label of variable
     * @param int $min The minimum number range
     * @param int $max The maximum number range
     * @param bool $required
     *
     */
    private function validateNumeric($var, $label, $min = 0, $max = 0, $required = false)
    {
        if ($required == false && mb_strlen($var, 'utf-8') == 0) {
            return true;
        }

        if (filter_var($var, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]) === false) {
            $this->addError($label);
        }
    }

    /**
     * validate a equality
     * @param array $var List of variables
     * @param mixed $label The label of variable
     */
    private function validateEqual($var, $label)
    {
        if (is_array($var) && count($var) == 2 && $var[0] === $var[1]) {
            return true;
        } else {
            $this->addError($label);
        }
    }

    /**
     * validate the inequality
     * @param array $var List of variables
     * @param mixed $label The label of variable
     */
    private function validateNotEqual($var, $label)
    {
        if (is_array($var) && count($var) == 2 && $var[0] !== $var[1]) {
            return true;
        } else {
            $this->addError($label);
        }
    }

    /**
     * validate is empty
     * @param string $var The variable
     * @param mixed $label The label of variable
     */
    private function validateEmpty($var, $label)
    {
        if (!empty($var)) {
            $this->addError($label);
        }
    }

    /**
     * validate is not empty
     * @param string $var The variable
     * @param mixed $label The label of variable
     */
    private function validateNotEmpty($var, $label)
    {
        if (empty($var)) {
            $this->addError($label);
        }
    }

    /**
     * validate is InArray
     * @param array $var List of variables
     * @param mixed $label The label of variable
     */
    private function validateIn($var, $label)
    {
        if (is_array($var) && count($var) == 2 && in_array($var[0], $var[1], true)) {
            return true;
        } else {
            $this->addError($label);
        }
    }

    /**
     * validate on a regular expression
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param bool $required
     */
    private function validateRegex($var, $label, $required = false)
    {
        if (is_array($var) && count($var) == 2 && $required == false && mb_strlen($var[0], 'utf-8') == 0) {
            return true;
        }

        if (!preg_match($var[1], $var[0])) {
            $this->addError($label);
        }
    }

    /**
     * validate a floating point number
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param bool $required
     */

    private function validateFloat($var, $label, $required = false)
    {
        if ($required == false && mb_strlen($var, 'utf-8') == 0) {
            return true;
        }

        if (filter_var($var, FILTER_VALIDATE_FLOAT) === false) {
            $this->addError($label);
        }
    }

    /**
     * validate a url
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param bool $required
     */
    private function validateUrl($var, $label, $required = false)
    {
        if ($required == false && mb_strlen($var, 'utf-8') == 0) {
            return true;
        }

        if (!preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $var)) {
            $this->addError($label);
        }
    }

    /**
     * validate a email address
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param bool $required
     */
    private function validateEmail($var, $label, $required = false)
    {
        if ($required == false && mb_strlen($var, 'utf-8') == 0) {
            return true;
        }

        if (!preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $var)) {
            $this->addError($label);
        }
    }

    /**
     * validate a boolean
     * @param string $var The variable
     * @param mixed $label The label of variable
     * @param bool $required
     */
    private function validateBool($var, $label)
    {
        if (filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
            $this->addError($label);
        }
    }

    /**
     * validate custom
     * @param string $condition The condition
     * @param mixed $label The label of variable
     */
    private function validateCustom($condition, $label)
    {
        if (!$condition) {
            $this->addError($label);
        }
    }
}
