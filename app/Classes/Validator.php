<?php

declare(strict_types=1);

namespace App\Classes;

use Illuminate\Http\UploadedFile;

/**
 * Class Validation data
 *
 * @license Code and contributions have MIT License
 * @link    https://visavi.net
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
     * @param mixed $input
     * @param int   $min
     * @param int   $max
     * @param mixed $label
     * @param bool  $required
     *
     * @return Validator
     */
    public function length($input, int $min, int $max, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        $input = (string) $input;

        if (mb_strlen($input, 'utf-8') < $min) {
            $this->addError($label, __('validator.length_min', ['length' => $min]));
        } elseif (mb_strlen($input, 'utf-8') > $max) {
            $this->addError($label, __('validator.length_max', ['length' => $max]));
        }

        return $this;
    }

    /**
     * Проверяет число на вхождение в диапазон
     *
     * @param int|float $input
     * @param int|float $min
     * @param int|float $max
     * @param mixed     $label
     *
     * @return Validator
     */
    public function between($input, $min, $max, $label): Validator
    {
        if ($input < $min || $input > $max) {
            $this->addError($label, __('validator.between', ['min' => $min, 'max' => $max]));
        }

        return $this;
    }

    /**
     * Проверяет на больше чем число
     *
     * @param int|float $input
     * @param int|float $input2
     * @param mixed     $label
     *
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
     * @param int|float $input
     * @param int|float $input2
     * @param mixed     $label
     *
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
     * @param int|float $input
     * @param int|float $input2
     * @param mixed     $label
     *
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
     * @param int|float $input
     * @param int|float $input2
     * @param mixed     $label
     *
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
     * @param mixed $input
     * @param mixed $input2
     * @param mixed $label
     *
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
     * @param mixed $input
     * @param mixed $input2
     * @param mixed $label
     *
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
     * @param mixed $input
     * @param mixed $label
     *
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
     * @param mixed $input
     * @param mixed $label
     *
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
     * @param mixed $input
     * @param mixed $label
     *
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
     * @param mixed $input
     * @param mixed $label
     *
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
     * @param mixed $input
     * @param array $haystack
     * @param mixed $label
     *
     * @return Validator
     */
    public function in($input, array $haystack, $label): Validator
    {
        if (! is_array($haystack) || ! in_array($input, $haystack, true)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет на не вхождение в массив
     *
     * @param mixed $input
     * @param array $haystack
     * @param mixed $label
     *
     * @return Validator
     */
    public function notIn($input, array $haystack, $label): Validator
    {
        if (! is_array($haystack) || in_array($input, $haystack, true)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет по регулярному выражению
     *
     * @param mixed  $input
     * @param string $pattern
     * @param mixed  $label
     * @param bool   $required
     *
     * @return Validator
     */
    public function regex($input, string $pattern, $label, bool $required = true): Validator
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
     * Check float
     *
     * @param mixed $input
     * @param mixed $label
     * @param bool  $required
     *
     * @return Validator
     */
    public function float($input, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! is_float($input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет адрес сайта
     *
     * @param mixed $input
     * @param mixed $label
     * @param bool  $required
     *
     * @return Validator
     */
    public function url($input, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! preg_match('|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', $input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет email
     *
     * @param mixed $input
     * @param mixed $label
     * @param bool  $required
     *
     * @return Validator
     */
    public function email($input, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (filter_var($input, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Check IP address
     *
     * @param mixed $input
     * @param mixed $label
     * @param bool  $required
     *
     * @return Validator
     */
    public function ip($input, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (filter_var($input, FILTER_VALIDATE_IP) === false) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Check phone
     *
     * @param mixed $input
     * @param mixed $label
     * @param bool  $required
     *
     * @return Validator
     */
    public function phone($input, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! preg_match('#^\d{8,13}$#', $input)) {
            $this->addError($label);
        }

        return $this;
    }

    /**
     * Проверяет файл
     *
     * @param UploadedFile|null $input
     * @param array             $rules
     * @param mixed             $label
     * @param bool              $required
     *
     * @return Validator
     */
    public function file(?UploadedFile $input, array $rules, $label, bool $required = true): Validator
    {
        if (! $required && blank($input)) {
            return $this;
        }

        if (! $input instanceof UploadedFile) {
            $this->addError($label);
            return $this;
        }

        if (! $input->isValid()) {
            $this->addError($input->getErrorMessage());
            return $this;
        }

        $key = is_array($label) ? key($label) : 0;

        if (empty($rules['extensions'])) {
            $rules['extensions'] = ['jpg', 'jpeg', 'gif', 'png'];
        }

        $extension = strtolower($input->getClientOriginalExtension());

        if (! in_array($extension, $rules['extensions'], true)) {
            $this->addError([$key => __('validator.extension')]);
        }

        if (isset($rules['maxsize']) && $input->getSize() > $rules['maxsize']) {
            $this->addError([$key => __('validator.size_max', ['size' => formatSize($rules['maxsize'])])]);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'gif', 'png'], true)) {
            [$width, $height] = getimagesize($input->getPathname());

            if (isset($rules['maxweight'])) {
                if ($width > $rules['maxweight'] || $height > $rules['maxweight']) {
                    $this->addError([$key => __('validator.weight_max', ['weight' => $rules['maxweight']])]);
                }
            }

            if (isset($rules['minweight'])) {
                if ($width < $rules['minweight'] || $height < $rules['minweight']) {
                    $this->addError([$key => __('validator.weight_min', ['weight' => $rules['minweight']])]);
                }
            } elseif (empty($width) || empty($height)) {
                $this->addError([$key => __('validator.weight_empty')]);
            }
        }

        return $this;
    }

    /**
     * Добавляет ошибки в массив
     *
     * @param mixed       $error       текст ошибки
     * @param string|null $description
     *
     * @return void
     */
    public function addError($error, ?string $description = null): void
    {
        $key = 0;

        if (is_array($error)) {
            $key   = key($error);
            $error = current($error);
        }

        if (isset($this->errors[$key])) {
            $this->errors[] = trim($error . ' ' . $description);
        } else {
            $this->errors[$key] = trim($error . ' ' . $description);
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
