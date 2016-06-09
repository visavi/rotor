<?php

class BBCodeParser {

	public $parsers = [
		'code' => [
			'pattern' => '/\[code\](.*?)\[\/code\]/s',
			'callback' => 'highlightCode'
		],
		'bold' => [
			'pattern' => '/\[b\](.*?)\[\/b\]/s',
			'replace' => '<strong>$1</strong>',
		],
		'italic' => [
			'pattern' => '/\[i\](.*?)\[\/i\]/s',
			'replace' => '<em>$1</em>',
		],
		'underLine' => [
			'pattern' => '/\[u\](.*?)\[\/u\]/s',
			'replace' => '<u>$1</u>',
		],
		'lineThrough' => [
			'pattern' => '/\[s\](.*?)\[\/s\]/s',
			'replace' => '<strike>$1</strike>',
		],
		'fontSize' => [
			'pattern' => '/\[size\=([1-5])\](.*?)\[\/size\]/s',
			'replace' => '<font size="$1">$2</font>',
		],
		'fontColor' => [
			'pattern' => '/\[color\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.*?)\[\/color\]/s',
			'replace' => '<font color="$1">$2</font>',
			'iterate' => 1,
		],
		'center' => [
			'pattern' => '/\[center\](.*?)\[\/center\]/s',
			'replace' => '<div style="text-align:center;">$1</div>',
		],
		'quote' => [
			'pattern' => '/\[quote\](.*?)\[\/quote\]/s',
			'replace' => '<blockquote>$1</blockquote>',
			'iterate' => 3,
		],
		'namedQuote' => [
			'pattern' => '/\[quote\=(.*?)\](.*)\[\/quote\]/s',
			'replace' => '<blockquote>$2<small>$1</small></blockquote>',
			'iterate' => 3,
		],
		'http' => [
			'pattern' => '%\b((?<!(=|]))([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))%s',
			'replace' => '<a href="$1">$1</a>',
		],
		'link' => [
			'pattern' => '/\[url\](.*?)\[\/url\]/s',
			'replace' => '<a href="$1">$1</a>',
		],
		'namedLink' => [
			'pattern' => '/\[url\=(.*?)\](.*?)\[\/url\]/s',
			'replace' => '<a href="$1">$2</a>',
		],
		'image' => [
			'pattern' => '/\[img\](.*?)\[\/img\]/s',
			'callback' => 'imgReplace',
		],
	/*	'orderedList' => [
			'pattern' => '/\[list=1\](.*?)\[\/list\]/s',
			'replace' => '<ol>$1</ol>',
		],
		'unorderedList' => [
			'pattern' => '/\[list\](.*?)\[\/list\]/s',
			'replace' => '<ul>$1</ul>',
		],
		'listItem' => [
			'pattern' => '/\[\*\](.*)/',
			'replace' => '<li>$1</li>',
		],*/
		'spoiler' => [
			'pattern' => '/\[spoiler\](.*?)\[\/spoiler\]/s',
			'callback' => 'spoilerText',
			'iterate' => 1,
		],
		'shortSpoiler' => [
			'pattern' => '/\[spoiler\=(.*?)\](.*?)\[\/spoiler\]/s',
			'callback' => 'spoilerText',
			'iterate' => 1,
		],
		'hide' => [
			'pattern' => '/\[hide\](.*?)\[\/hide\]/s',
			'callback' => 'hiddenText',
		],
		'youtube' => [
			'pattern' => '/\[youtube\](.*?)\[\/youtube\]/s',
			'replace' => '<iframe width="320" height="240" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
		],
	];

	/**
	 * Метод парсинга BBCode
	 * @param  string $source текст содержаший BBCode
	 * @return string распарсенный текст
	 */
	public function parse($source) {
		$source = nl2br($source);

		foreach ($this->parsers as $parser) {

			$iterate = isset($parser['iterate']) ? $parser['iterate'] : 0;

			for ($i = 0; $i <= $iterate; $i++) {
				if (isset($parser['callback'])) {
					$source = preg_replace_callback($parser['pattern'], [$this, $parser['callback']], $source);
				} else {
					$source = preg_replace($parser['pattern'], $parser['replace'], $source);
				}
			}
		}
		return $source;
	}

	public function clear($source) {
		return $source = preg_replace('/\[(.*?)\]/', '', $source);
	}

	/**
	 * Обработка изображений
	 * @param  array $match ссылка на изображение
	 * @return string картинка
	 */
	public function imgReplace($match) {
		if (preg_match('/[\w\-]+\.(jpg|png|gif|jpeg)/', $match[1])) {
			return '<img src="'.$match[1].'" class="img-responsive img-message" alt="image">';
		} else {
			return $match[1];
		}
	}

	/**
	 * Подсветка кода
	 * @param callable $match массив элементов
	 * @return string текст с подсветкой
	 */
	public function highlightCode($match) {

		//Чтобы bb-код и смайлы не работали внутри тега [code]
		$match[1] = strtr($match[1], [':' => '&#58;', '[' => '&#91;']);

		return '<pre class="prettyprint linenums">'.$match[1].'</pre>';
	}

	/**
	 * Скрытие текста под спойлер
	 * @param callable $match массив элементов
	 * @return string код спойлера
	 */
	public function spoilerText($match) {

		$title = (empty($match[1]) || !isset($match[2])) ? 'Развернуть для просмотра' : $match[1];
		$text = (empty($match[2])) ? !isset($match[2]) ? $match[1] : 'Текст отсуствует' : $match[2];

		return '<div class="spoiler">
				<b class="spoiler-title">'.$title.'</b>
				<div class="spoiler-text" style="display: none;">'.$text.'</div>
			</div>';
	}

	/**
	 * Скрытие текста от неавторизованных пользователей
	 * @param callable $match массив элементов
	 * @return string  скрытый код
	 */
	public function hiddenText($match) {

		if (empty($match[1])) $match[1] = 'Текст отсутствует';

		return '<div class="hiding">
				<span class="strong">Скрытый контент:</span> '.(User::check() ? $match[1] : 'Для просмотра необходимо авторизоваться!').
				'</div>';
	}

	/**
	 * Обработка смайлов
	 * @param  string  $text  Необработанный текст
	 * @return string         Обработанный текст
	 */
	public function parseSmiles($source)
	{
		global $config;
		static $list_smiles;

		if (empty($list_smiles)) {
			if (! file_exists(DATADIR.'/temp/smiles.dat')) {

				$smiles = DBM::run()->query("SELECT `smiles_name`, `smiles_code` FROM `smiles` ORDER BY CHAR_LENGTH(`smiles_code`) DESC;");
				file_put_contents(DATADIR.'/temp/smiles.dat', serialize($smiles));
			}

			$list_smiles = unserialize(file_get_contents(DATADIR."/temp/smiles.dat"));
		}

		$count = 0;
		foreach($list_smiles as $smile) {
			$source = preg_replace('|'.preg_quote($smile['smiles_code']).'|', '<img src="/images/smiles/'.$smile['smiles_name'].'" alt="'.$smile['smiles_name'].'" /> ', $source, $config['resmiles'] - $count, $cnt);
			$count += $cnt;
			if ($count >= $config['resmiles']) break;
		}
		return $source;
	}

	/**
	 * Sets the parser pattern and replace.
	 * This can be used for new parsers or overwriting existing ones.
	 * @param string $name Parser name
	 * @param string $pattern Pattern
	 * @param string $replace Replace pattern
	 */
	public function setParser($name, $pattern, $replace) {

		$this->parsers[$name] = [
			'pattern' => $pattern,
			'replace' => $replace
		];
	}

	/**
	 * Limits the parsers to only those you specify
	 * @param  mixed $only parsers
	 * @return object BBCodeParser object
	 */
	public function only($only = null) {

		$only = (is_array($only)) ? $only : func_get_args();
		$this->parsers = $this->arrayOnly($only);
		return $this;
	}

	/**
	 * Removes the parsers you want to exclude
	 * @param  mixed $except parsers
	 * @return object BBCodeParser object
	 */
	public function except($except = null) {

		$except = (is_array($except)) ? $except : func_get_args();
		$this->parsers = $this->arrayExcept($except);
		return $this;
	}

	/**
	 * List of all available parsers
	 * @return array array of available parsers
	 */
	public function getAvailableParsers() {

		return $this->availableParsers;
	}

	/**
	 * List of chosen parsers
	 * @return array array of parsers
	 */
	public function getParsers() {

		return $this->parsers;
	}

	/**
	 * Filters all parsers that you don´t want
	 * @param  array $only chosen parsers
	 * @return array parsers
	 */
	private function arrayOnly($only) {

		return array_intersect_key($this->parsers, array_flip((array) $only));
	}

	/**
	 * Removes the parsers that you don´t want
	 * @param  array $except parsers to exclude
	 * @return array parsers
	 */
	private function arrayExcept($excepts) {

		return array_diff_key($this->parsers, array_flip((array) $excepts));
	}

}
