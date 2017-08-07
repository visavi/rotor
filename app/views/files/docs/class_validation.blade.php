<?php //show_title('Class Validation'); ?>

<b>Класс для валидации входящих данных</b>

<h3>addRule(string $type, mixed $var, string $label, bool $required = false, int $min = 0, int $max = 0)</h3>

Пример проверки регистрации пользователей
<pre class="prettyprint linenums">
$validation = new Validation();

$validation -> addRule('equal', array($provkod, $_SESSION['protect']), 'Проверочное число не совпало с данными на картинке!')
	-> addRule('regex', array($logs, '|^[a-z0-9\-]+$|i'), 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!', true)
	-> addRule('regex', array($pars, '|^[a-z0-9\-]+$|i'), 'Недопустимые символы в пароле. Разрешены знаки латинского алфавита, цифры и дефис!', true)
	-> addRule('email', $meil, 'Вы ввели неверный адрес email, необходим формат name@site.domen!')
	-> addRule('string', $invite, 'Слишком длинный или короткий пригласительный ключ!', Setting::get('invite'), 15, 20)
	-> addRule('string', $logs, 'Слишком длинный или короткий логин!', true, 3, 20)
	-> addRule('string', $pars, 'Слишком длинный или короткий пароль!',  true, 6, 20)
	-> addRule('equal', array($pars, $pars2), 'Ошибка! Введенные пароли отличаются друг от друга!')
	-> addRule('not_equal', array($logs, $pars), 'Пароль и логин должны отличаться друг от друга!');

if ($validation->run()){
	echo 'Все отлично, ошибок нет!';
} else {
	show_error($validation->getErrors());
}
</pre>

<b>Список допустимых параметров $type</b>
<ul>
	<li><a href="#validateString">string (validateString)</a> - проверяет длину текста</li>
	<li><a href="#validateNumeric">numeric (validateNumeric)</a> - проверяет число подходящее под условие "больше чем N и меньше чем M"</li>
	<li><a href="#validateMax">max (validateMax) - проверяет число подходящее под условие "больше чем N"</li>
	<li><a href="#validateMin">min (validateMin)</a> - проверяет число подходящее под условие "меньше чем N"</li>
	<li><a href="#validateEqual">equal (validateEqual)</a> - провеяет строку на эквивалентность</li>
	<li><a href="#validateNotEqual">not_equal (validateNotEqual)</a> - провеяет строку на НЕ эквивалентность</li>
	<li><a href="#validateEmpty">empty (validateEmpty)</a> - проверяет строку на пустоту</li>
	<li><a href="#validateNotEmpty">not_empty (validateNotEmpty)</a> - проверяет строку на НЕ пустоту</li>
	<li><a href="#validateIn">in (validateIn)</a> - проверяет строку на нахождение в переданном списке</li>
	<li><a href="#validateRegex">regex (validateRegex)</a> - проверяет строку на регулярное выражение</li>
	<li><a href="#validateFloat">float (validateFloat)</a> - проверяет строку на соответствие числу с плавающей точкой</li>
	<li><a href="#validateUrl">url (validateUrl)</a> - проверяет строку на соответствие адресной ссылке</li>
	<li><a href="#validateEmail">email (validateEmail)</a> - проверяет строку на соответствие адресу электронной почты</li>
	<li><a href="#validateBool">bool (validateBool)</a> - проверяет строку на логический тип</li>
	<li><a href="#validateCustom">custom (validateCustom)</a> - Выполняет пользовательскую проверку данных</li>
</ul>

<h3>run()</h3>
Запускает проверку и возвращает true если все условия выполнены или массив со списком ошибок<br />

<pre class="prettyprint linenums">
if ($validation->run()){
	echo 'Все отлично, ошибок нет!';
} else {
	show_error($validation->getErrors());
}
</pre>

<h3>addError($error)</h3>
Добавляет ошибку в список<br />
Используется для пользовательской проверки некоторых данных<br />
Аналогично фильтру <a href="#validateCustom">custom (validateCustom)</a>

<pre class="prettyprint linenums">
if (substr_count($logs, '-') > 2) {
	$validation -> addError('Запрещено использовать в логине слишком много дефисов!');
}
</pre>

<h3>getErrors()</h3>
Выводит массив ошибок, если массив пустой ошибок нет

<h3 id="validateString">validateString</h3>
<pre class="prettyprint linenums">
addRule('string', $invite, 'Слишком длинный или короткий пригласительный ключ!', true, 15, 20)
</pre>
Если передан required = false, то валидация сработает при пустой строке

<h3 id="validateNumeric">validateNumeric</h3>
<pre class="prettyprint linenums">
addRule('numeric', $count, 'Слишком больше или маленькое число', true, 5, 5000)
</pre>
Если передан required = false, то валидация сработает при пустой строке

<h3 id="validateMax">validateMax</h3>
<pre class="prettyprint linenums">
addRule('max', array(App::user('point'), Setting::get('eventpoint')), 'У вас недостаточно актива для создания события!')
</pre>

<h3 id="validateMin">validateMin</h3>
<pre class="prettyprint linenums">
addRule('min', array(App::user('timenickname'), SITETIME), 'Изменять ник можно не чаще чем 1 раз в сутки!')
</pre>

<h3 id="validateEqual">validateEqual</h3>
<pre class="prettyprint linenums">
addRule('equal', array($pars, $pars2), 'Ошибка! Введенные пароли отличаются друг от друга!')
</pre>

<h3 id="validateNotEqual">validateNotEqual</h3>
<pre class="prettyprint linenums">
addRule('not_equal', array($logs, $pars), 'Пароль и логин должны отличаться друг от друга!')
</pre>

<h3 id="validateEmpty">validateEmpty</h3>
<pre class="prettyprint linenums">
addRule('empty', $forums['closed'], 'В данном разделе запрещено создавать темы!')
</pre>

<h3 id="validateNotEmpty">validateNotEmpty</h3>
<pre class="prettyprint linenums">
addRule('not_empty', $forums, 'Раздела для новой темы не существует!')
</pre>

<h3 id="validateIn">validateIn</h3>
<pre class="prettyprint linenums">
addRule('in', array('jpg', array('gif', 'png', 'jpg', 'jpeg')), 'Недопустимое расширение файла!')
</pre>

<h3 id="validateRegex">validateRegex</h3>
<pre class="prettyprint linenums">
addRule('regex', array($skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#'), 'Недопустимый формат Skype, только латинские символы от 6 до 32!', false)
</pre>
Если передан required = false, то валидация сработает при пустой строке

<h3 id="validateFloat">validateFloat</h3>
<pre class="prettyprint linenums">
addRule('float', '0.75', 'Необходимо указывать сумму с копейками!', false)
</pre>
Если передан required = false, то валидация сработает при пустой строке

<h3 id="validateUrl">validateUrl</h3>
<pre class="prettyprint linenums">
addRule('url', 'http://visavi.net', 'Неверный формат адреса сайта!', true)
</pre>
Если передан required = false, то валидация сработает при пустой строке

<h3 id="validateEmail">validateEmail</h3>
<pre class="prettyprint linenums">
addRule('email', $meil, 'Неправильный адрес email, необходим формат name@site.domen!', true)
</pre>
Если передан required = false, то валидация сработает при пустой строке

<h3 id="validateBool">validateBool</h3>
<pre class="prettyprint linenums">
addRule('bool', 1, 'Необходимо дать свое согласие!')
</pre>
Возвращает true для значений "1", "true", "on" и "yes". Иначе возвращает false.

<h3 id="validateCustom">validateCustom</h3>
Пользовательская проверка данных, подходит для выполнения условий которые не подходят под вышеперечисленные условия
<pre class="prettyprint linenums">
addRule('custom', strcmp($var1, $var2) !== 0, 'Данные не равны при регистрозависимом сравнении!')
</pre>
Возвращает true если условие будет выполнено

<br /><br />
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br />
