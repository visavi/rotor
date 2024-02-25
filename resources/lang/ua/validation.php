<?php

return [
    /*
    |------------------------------------------------- -------------------------
    | Validation Language Lines
    |------------------------------------------------- -------------------------
    |
    | Наступні language lines contain the default error messages used by
    | the validator class. Один з цих правил має багато версій так
    | as the size rules. Feel free to tweak each of thes messages here.
    |
    */

    'accepted'        => 'Ви повинні прийняти :attribute.',
    'accepted_if'     => 'The :attribute must be accepted when :other is :value.',
    'active_url'      => 'Поле :attribute містить недійсний URL.',
    'after'           => 'У полі :attribute має бути дата більша :date.',
    'after_or_equal'  => 'У полі :attribute має бути дата більша або дорівнювати :date.',
    'alpha'           => 'Поле :attribute може містити лише літери.',
    'alpha_dash'      => 'Поле :attribute може містити лише літери, цифри, дефіс та нижнє підкреслення.',
    'alpha_num'       => 'Поле :attribute може містити лише літери та цифри.',
    'array'           => 'Поле :attribute має бути масивом.',
    'attached'        => 'Поле :attribute вже прикріплено.',
    'before'          => 'У полі :attribute має бути дата раніше :date.',
    'before_or_equal' => 'У полі :attribute має бути дата раніше або дорівнювати :date.',
    'between'         => [
        'array'   => 'Кількість елементів у полі :attribute має бути між :min і :max.',
        'file'    => 'Розмір файлу в полі :attribute повинен бути між :min та :max Кілобайт(а).',
        'numeric' => 'Поле :attribute має бути між :min та :max.',
        'string'  => 'Кількість символів у полі :attribute має бути між :min та :max.',
    ],
    'boolean'          => 'Поле :attribute повинно мати значення логічного типу.',
    'confirmed'        => 'Поле :attribute не збігається з підтвердженням.',
    'current_password' => 'The password is incorrect.',
    'date'             => 'Поле :attribute не є датою.',
    'date_equals'      => 'Поле :attribute має бути датою рівною :date.',
    'date_format'      => 'Поле :attribute не відповідає формату :format.',
    'different'        => 'Поля :attribute і :other повинні відрізнятися.',
    'digits'           => 'Довжина цифрового поля :attribute повинна бути :digits.',
    'digits_between'   => 'Довжина цифрового поля :attribute повинна бути між :min та :max.',
    'dimensions'       => 'Поле :attribute має неприпустимі розміри зображення.',
    'distinct'         => 'Поле :attribute містить значення, що повторюється.',
    'email'            => 'Поле :attribute має бути дійсною електронною адресою.',
    'ends_with'        => 'Поле :attribute має закінчуватися одним із наступних значень: :values',
    'exists'           => 'Вибране значення для :attribute неправильне.',
    'file'             => 'Поле :attribute має бути файлом.',
    'filled'           => 'Поле :attribute обов\'язково для заповнення.',
    'gt'               => [
        'array'   => 'Кількість елементів у полі :attribute має бути більшою :value.',
        'file'    => 'Розмір файлу в полі :attribute повинен бути більшим :value Кілобайт(а).',
        'numeric' => 'Поле :attribute має бути більше :value.',
        'string'  => 'Кількість символів у полі :attribute має бути більшою :value.',
    ],
    'gte' => [
        'array'   => 'Кількість елементів у полі :attribute має бути :value або більше.',
        'file'    => 'Розмір файлу в полі :attribute повинен бути :value Кілобайт(а) або більше.',
        'numeric' => 'Поле :attribute має бути :value або більше.',
        'string'  => 'Кількість символів у полі :attribute має бути :value або більше.',
    ],
    'image'    => 'Поле :attribute має бути зображенням.',
    'in'       => 'Вибране значення для :attribute помилкове.',
    'in_array' => 'Поле :attribute не існує в :other.',
    'integer'  => 'Поле :attribute має бути цілим числом.',
    'ip'       => 'Поле :attribute має бути дійсною IP-адресою.',
    'ipv4'     => 'Поле :attribute має бути дійсною IPv4-адресою.',
    'ipv6'     => 'Поле :attribute має бути дійсною IPv6-адресою.',
    'json'     => 'Поле :attribute має бути рядком JSON.',
    'lt'       => [
        'array'   => 'Кількість елементів у полі :attribute має бути меншою :value.',
        'file'    => 'Розмір файлу в полі :attribute повинен бути меншим :value Кілобайт(а).',
        'numeric' => 'Поле :attribute має бути менше :value.',
        'string'  => 'Кількість символів у полі :attribute має бути меншою :value.',
    ],
    'lte' => [
        'array'   => 'Кількість елементів у полі :attribute має бути :value або менше.',
        'file'    => 'Розмір файлу в полі :attribute повинен бути :value Кілобайт(а) або менше.',
        'numeric' => 'Поле :attribute має бути :value або менше.',
        'string'  => 'Кількість символів у полі :attribute має бути :value або менше.',
    ],
    'max' => [
        'array'   => 'Кількість елементів у полі :attribute не може перевищувати :max.',
        'file'    => 'Розмір файлу в полі :attribute не може бути більшим :max Кілобайт(а).',
        'numeric' => 'Поле :attribute не може бути більше :max.',
        'string'  => 'Кількість символів у полі :attribute не може перевищувати :max.',
    ],
    'mimes'     => 'Поле :attribute має бути файлом одного з таких типів: :values.',
    'mimetypes' => 'Поле :attribute має бути файлом одного з таких типів: :values.',
    'min'       => [
        'array'   => 'Кількість елементів у полі :attribute повинна бути не меншою :min.',
        'file'    => 'Розмір файлу в полі :attribute повинен бути не меншим :min Кілобайт(а).',
        'numeric' => 'Поле :attribute має бути не менше :min.',
        'string'  => 'Кількість символів у полі :attribute повинна бути не меншою :min.',
    ],
    'multiple_of'          => 'Значення поля :attribute має бути кратним :value',
    'not_in'               => 'Вибране значення для :attribute помилкове.',
    'not_regex'            => 'Вибраний формат для :attribute помилковий.',
    'numeric'              => 'Поле :attribute має бути числом.',
    'password'             => 'Неправильний пароль.',
    'present'              => 'Поле :attribute має бути присутнім.',
    'prohibited'           => 'Поле :attribute заборонено.',
    'prohibited_if'        => 'Поле :attribute заборонено, коли :other одно :value.',
    'prohibited_unless'    => 'Поле :attribute заборонено, якщо :other не входить до :values.',
    'regex'                => 'Поле :attribute має помилковий формат.',
    'relatable'            => 'Поле :attribute не може бути пов\'язане з цим ресурсом.',
    'required'             => 'Поле :attribute обов\'язково для заповнення.',
    'required_if'          => 'Поле :attribute обов\'язково для заповнення, коли :other одно :value.',
    'required_unless'      => 'Поле :attribute обов\'язково для заповнення, коли :other не дорівнює :values.',
    'required_with'        => 'Поле :attribute обов\'язково для заповнення, коли :values вказано.',
    'required_with_all'    => 'Поле :attribute обов\'язково для заповнення, коли :values вказано.',
    'required_without'     => 'Поле :attribute обов\'язково для заповнення, коли :values не вказано.',
    'required_without_all' => 'Поле :attribute обов\'язково для заповнення, коли жодне з :values не вказано.',
    'same'                 => 'Значення полів :attribute і :other повинні збігатися.',
    'size'                 => [
        'array'   => 'Кількість елементів у полі :attribute повинна бути рівною :size.',
        'file'    => 'Розмір файлу в полі :attribute повинен дорівнювати :size Кілобайт(а).',
        'numeric' => 'Поле :attribute має бути рівним :size.',
        'string'  => 'Кількість символів у полі :attribute повинна бути рівною :size.',
    ],
    'starts_with' => 'Поле :attribute повинно починатися з одного з наступних значень: :values',
    'string'      => 'Поле :attribute має бути рядком.',
    'timezone'    => 'Поле :attribute має бути дійсним часовим поясом.',
    'unique'      => 'Таке значення поля :attribute вже існує.',
    'uploaded'    => 'Завантаження поля :attribute не вдалося.',
    'url'         => 'Поле :attribute має помилковий формат URL.',
    'uuid'        => 'Поле :attribute має бути коректним UUID.',

    /*
    |------------------------------------------------- -------------------------
    | Custom Validation Language Lines
    |------------------------------------------------- -------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | конвенція "attribute.rule" to name the lines. This makes it quick to
    | specificity a specific custom language line for given attribute rule.
    |
    */

    'custom' => [
        '_token' => [
            'in' => 'Невірний ідентифікатор сесії, повторіть дію!',
        ],
    ],

    /*
    |------------------------------------------------- -------------------------
    | Custom Validation Attributes
    |------------------------------------------------- -------------------------
    |
    | Наступні language lines є використані для звільнення нашого atribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". Це simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
];
