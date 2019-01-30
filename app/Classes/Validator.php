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
     * @return Validator
     */
    public function length($input, $min, $max, $label, $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        $input = htmlspecialchars_decode($input, ENT_QUOTES);

        if (mb_strlen($input, 'utf-8') < $min) {
            $this->addError($label, ' (Не менее ' . $min . ' симв.)');
        } elseif (mb_strlen($input, 'utf-8') > $max) {
            $this->addError($label, ' (Не более ' . $max . ' симв.)');
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
     * @return Validator
     */
    public function between($input, $min, $max, $label): Validator
    {
        if ($input < $min || $input > $max) {
            $this->addError($label, ' (От ' . $min . ' до ' . $max . ')');
        }

        return $this;
    }

    /**
     * Проверяет на больше чем число
     *
     * @param  int   $input
     * @param  int   $input2
     * @param  mixed $label
     * @return Validator
     */
    public function gt($input, $input2, $label): Validator
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
     * @return Validator
     */
    public function gte($input, $input2, $label): Validator
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
     * @return Validator
     */
    public function lt($input, $input2, $label): Validator
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
     * @return Validator
     */
    public function lte($input, $input2, $label): Validator
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
     * @return Validator
     */
    public function equal($input, $input2, $label): Validator
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
     * @return Validator
     */
    public function notEqual($input, $input2, $label): Validator
    {
        if ($input === $input2) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет пустые ли данные
     *
     * @param  mixed $input
     * @param  mixed $label
     * @return Validator
     */
    public function empty($input, $label): Validator
    {
        if (! empty($input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет не пустые ли данные
     *
     * @param  mixed $input
     * @param  mixed $label
     * @return Validator
     */
    public function notEmpty($input, $label): Validator
    {
        if (empty($input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на true
     *
     * @param  mixed $input
     * @param  mixed $label
     * @return Validator
     */
    public function true($input, $label): Validator
    {
        if (filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на false
     *
     * @param  mixed $input
     * @param  mixed $label
     * @return Validator
     */
    public function false($input, $label): Validator
    {
        if (filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на вхождение в массив
     *
     * @param  mixed $input
     * @param  array $haystack
     * @param  mixed $label
     * @return Validator
     */
    public function in($input, $haystack, $label): Validator
    {
        if (! \is_array($haystack) || ! \in_array($input, $haystack, true)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на не вхождение в массив
     *
     * @param  mixed $input
     * @param  array $haystack
     * @param  mixed $label
     * @return Validator
     */
    public function notIn($input, $haystack, $label): Validator
    {
        if (! \is_array($haystack) || \in_array($input, $haystack, true)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет по регулярному выражению
     *
     * @param  string $input
     * @param  string $pattern
     * @param  mixed  $label
     * @param  bool   $required
     * @return Validator
     */
    public function regex($input, $pattern, $label, $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! preg_match($pattern, $input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Тестирует на число в плавающей точкой
     *
     * @param  float $input
     * @param  mixed $label
     * @param  bool  $required
     * @return Validator
     */
    public function float($input, $label, $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! \is_float($input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет адрес сайта
     *
     * @param  string $input
     * @param  mixed  $label
     * @param  bool   $required
     * @return Validator
     */
    public function url($input, $label, $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет email
     *
     * @param  string $input
     * @param  mixed  $label
     * @param  bool   $required
     * @return Validator
     */
    public function email($input, $label, $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет файл
     *
     * @param \Illuminate\Http\UploadedFile|null $input
     * @param array                              $rules
     * @param mixed                              $label
     * @param bool                               $required
     * @return Validator
     */
    public function file($input, $rules, $label, $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! ($input instanceof \Illuminate\Http\UploadedFile) || ! $input->isValid()) {
            $this->addError($label);
            return $this;
        }

        $key = \is_array($label) ? key($label) : 0;

        if (empty($rules['extensions'])) {
            $rules['extensions'] = ['jpg', 'jpeg', 'gif', 'png'];
        }

        $extension = strtolower($input->getClientOriginalExtension());

        if (! \in_array($extension, $rules['extensions'], true)) {
            $this->addError([$key => 'Недопустимое расширение файла!']);
        }

        if (isset($rules['maxsize']) && $input->getSize() > $rules['maxsize']) {
            $this->addError([$key => 'Максимальный вес файла ' . formatSize($rules['maxsize']) . '!']);
        }

        if (\in_array($extension, ['jpg', 'jpeg', 'gif', 'png'], true)) {
            [$width, $height] = getimagesize($input);

            if (isset($rules['maxweight'])) {
                if ($width > $rules['maxweight'] || $height > $rules['maxweight']) {
                    $this->addError([$key => 'Максимальный размер картинки '. $rules['maxweight'] . 'px!']);
                }
            }

            if (isset($rules['minweight'])) {
                if ($width < $rules['minweight'] || $height < $rules['minweight']) {
                    $this->addError([$key => 'Минимальный размер картинки ' . $rules['minweight'] . 'px!']);
                }
            } elseif (empty($width) || empty($height)) {
                $this->addError([$key => 'Размер картинки слишком маленький!']);
            }
        }

        return $this;
    }

    /**
     * Добавляет ошибки в массив
     *
     * @param  mixed  $error текст ошибки
     * @param  string $description
     * @return void
     */
    public function addError($error, $description = null): void
    {
        $key = 0;

        if (\is_array($error)) {
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
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Очищает список ошибок
     *
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Возвращает успешность валидации
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
