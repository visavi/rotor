<?php
/**
 * Класс обработки BB-кодов
 * @license Code and contributions have MIT License
 * @link    http://visavi.net
 * @author  Alexander Grigorev <visavi.net@mail.ru>
 */
class BBCodeParser {

	/**
	 * @var array
	 */
	protected $setting;

	/**
	 * @var array
	 */
	protected $parsers = array(
		'code' => array(
			'pattern' => '/\[code\](.*?)\[\/code\]/s',
			'callback' => 'highlightCode'
		),
		'bold' => array(
			'pattern' => '/\[b\](.*?)\[\/b\]/s',
			'replace' => '<strong>$1</strong>',
		),
		'italic' => array(
			'pattern' => '/\[i\](.*?)\[\/i\]/s',
			'replace' => '<em>$1</em>',
		),
		'underLine' => array(
			'pattern' => '/\[u\](.*?)\[\/u\]/s',
			'replace' => '<u>$1</u>',
		),
		'lineThrough' => array(
			'pattern' => '/\[s\](.*?)\[\/s\]/s',
			'replace' => '<strike>$1</strike>',
		),
		'fontSize' => array(
			'pattern' => '/\[size\=([1-5])\](.*?)\[\/size\]/s',
			'replace' => '<font size="$1">$2</font>',
		),
		'fontColor' => array(
			'pattern' => '/\[color\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.*?)\[\/color\]/s',
			'replace' => '<font color="$1">$2</font>',
			'iterate' => 5,
		),
		'center' => array(
			'pattern' => '/\[center\](.*?)\[\/center\]/s',
			'replace' => '<div style="text-align:center;">$1</div>',
		),
		'quote' => array(
			'pattern' => '/\[quote\](.*?)\[\/quote\]/s',
			'replace' => '<blockquote>$1</blockquote>',
			'iterate' => 3,
		),
		'namedQuote' => array(
			'pattern' => '/\[quote\=(.*?)\](.*?)\[\/quote\]/s',
			'replace' => '<blockquote>$2<small>$1</small></blockquote>',
			'iterate' => 3,
		),
		'http' => array(
			'pattern' => '%\b((?<!(=|]))[\w-]+://[^\s()<>\[\]]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))%s',
			'callback' => 'urlReplace',
		),
		'link' => array(
			'pattern' => '%\[url\](\b([\w-]+://[^\s()<>\[\]]+))\[/url\]%s',
			'callback' => 'urlReplace',
		),
		'namedLink' => array(
			'pattern' => '%\[url\=\b([\w-]+://[^\s()<>\[\]]+)\](.*?)\[/url\]%s',
			'callback' => 'urlReplace',
		),
		'image' => array(
			'pattern' => '%\[img\]\b([\w-]+://[^\s()<>\[\]]+\.(jpg|png|gif|jpeg))\[/img\]%s',
			'replace' => '<img src="$1" class="img-responsive" alt="image">',
		),
		'orderedList' => array(
			'pattern' => '/\[list=1\](.*?)\[\/list\]/s',
			'callback' => 'listReplace',
		),
		'unorderedList' => array(
			'pattern' => '/\[list\](.*?)\[\/list\]/s',
			'callback' => 'listReplace',
		),
		'spoiler' => array(
			'pattern' => '/\[spoiler\](.*?)\[\/spoiler\]/s',
			'callback' => 'spoilerText',
			'iterate' => 1,
		),
		'shortSpoiler' => array(
			'pattern' => '/\[spoiler\=(.*?)\](.*?)\[\/spoiler\]/s',
			'callback' => 'spoilerText',
			'iterate' => 1,
		),
		'hide' => array(
			'pattern' => '/\[hide\](.*?)\[\/hide\]/s',
			'callback' => 'hiddenText',
		),
		'youtube' => array(
			'pattern' => '/\[youtube\]([\w-]{11})\[\/youtube\]/s',
			'replace' => '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="//www.youtube.com/embed/$1"></iframe></div>',
		),
	);

	/**
	 * Конструктор
	 * @param string $setting настройки
	 */
	public function __construct($setting)
	{
		$this->setting = $setting;
	}

	/**
	 * Обрабатывает текст
	 * @param  string $source текст содержаший BBCode
	 * @return string         распарсенный текст
	 */
	public function parse($source)
	{
		$source = nl2br($source);

		foreach ($this->parsers as $parser) {

			$iterate = isset($parser['iterate']) ? $parser['iterate'] : 1;

			for ($i = 0; $i < $iterate; $i++) {
				if (isset($parser['callback'])) {
					$source = preg_replace_callback($parser['pattern'], array($this, $parser['callback']), $source);
				} else {
					$source = preg_replace($parser['pattern'], $parser['replace'], $source);
				}
			}
		}
		return $source;
	}

	/**
	 * Очищает текст от BB-кодов
	 * @param  string $source неочищенный текст
	 * @return string         очищенный текст
	 */
	public function clear($source)
	{
		return $source = preg_replace('/\[(.*?)\]/', '', $source);
	}

	/**
	 * Обработка ссылок
	 * @param  array  $match ссылка
	 * @return string        обработанная ссылка
	 */
	public function urlReplace($match)
	{
		$name   = isset($match[3]) ? $match[1] : $match[2];
		$target = (strpos($match[1], $this->setting['home']) === false) ? ' target="_blank" rel="nofollow"' : '';

		return '<a href="'.$match[1].'"'.$target.'>'.rawurldecode($name).'</a>';
	}

	/**
	 * Обработка списков
	 * @param  array  $match список
	 * @return string обработанный список
	 */
	public function listReplace($match)
	{
		$li = preg_split('/<br[^>]*>\R/', $match[1], -1, PREG_SPLIT_NO_EMPTY);
		if (empty($li)) return $match[0];

		$list = array();
		foreach($li as $l){
			$list[] = '<li>'.$l.'</li>';
		}

		$tag  = strpos($match[0], '[list]') === false ? 'ol' : 'ul';

		return '<'.$tag.'>'.implode($list).'</'.$tag.'>';
	}

	/**
	 * Подсветка кода
	 * @param  callable $match массив элементов
	 * @return string          текст с подсветкой
	 */
	public function highlightCode($match)
	{
		//Чтобы bb-код и смайлы не работали внутри тега [code]
		$match[1] = strtr($match[1], array(':' => '&#58;', '[' => '&#91;'));

		return '<pre class="prettyprint linenums">'.$match[1].'</pre>';
	}

	/**
	 * Скрытие текста под спойлер
	 * @param  callable $match массив элементов
	 * @return string          код спойлера
	 */
	public function spoilerText($match)
	{
		$title = (empty($match[1]) || !isset($match[2])) ? 'Развернуть для просмотра' : $match[1];
		$text = (empty($match[2])) ? !isset($match[2]) ? $match[1] : 'Текст отсутствует' : $match[2];

		return '<div class="spoiler">
				<b class="spoiler-title">'.$title.'</b>
				<div class="spoiler-text" style="display: none;">'.$text.'</div>
			</div>';
	}

	/**
	 * Скрытие текста от неавторизованных пользователей
	 * @param  callable $match массив элементов
	 * @return string          скрытый код
	 */
	public function hiddenText($match)
	{
		if (empty($match[1])) $match[1] = 'Текст отсутствует';

		return '<div class="hiding">
				<span class="strong">Скрытый контент:</span> '.(is_user() ? $match[1] : 'Для просмотра необходимо авторизоваться!').
				'</div>';
	}

	/**
	 * Обработка смайлов
	 * @param  string $text Необработанный текст
	 * @return string       Обработанный текст
	 */
	public function parseSmiles($source)
	{
		static $list_smiles;

		if (empty($list_smiles)) {
			if (! file_exists(STORAGE.'/temp/smiles.dat')) {

				$smiles = DBM::run()->query("SELECT smiles_code, smiles_name FROM smiles ORDER BY CHAR_LENGTH(smiles_code) DESC;");
				file_put_contents(STORAGE.'/temp/smiles.dat', serialize($smiles));
			}

			$list_smiles = unserialize(file_get_contents(STORAGE.'/temp/smiles.dat'));
		}

		$count = 0;
		foreach($list_smiles as $smile) {
			$source = preg_replace('|'.preg_quote($smile['smiles_code']).'|', '<img src="/upload/smiles/'.$smile['smiles_name'].'" alt="'.$smile['smiles_code'].'" /> ', $source, $this->setting['resmiles'] - $count, $cnt);
			$count += $cnt;
			if ($count >= $this->setting['resmiles']) break;
		}

		return $source;
	}

	/**
	 * Добавляет или переопределяет парсер.
	 * @param  string $name    Parser name
	 * @param  string $pattern Pattern
	 * @param  string $replace Replace pattern
	 * @return void
	 */
	public function setParser($name, $pattern, $replace)
	{
		$this->parsers[$name] = array(
			'pattern' => $pattern,
			'replace' => $replace
		);
	}

	/**
	 * Устанавливает список доступных парсеров
	 * @param  mixed  $only parsers
	 * @return object BBCodeParser object
	 */
	public function only($only = null)
	{
		$only = (is_array($only)) ? $only : func_get_args();
		$this->parsers = $this->arrayOnly($only);
		return $this;
	}

	/**
	 * Исключает парсеры из набора
	 * @param  mixed  $except parsers
	 * @return object BBCodeParser object
	 */
	public function except($except = null)
	{
		$except = (is_array($except)) ? $except : func_get_args();
		$this->parsers = $this->arrayExcept($except);
		return $this;
	}

	/**
	 * Возвращает список всех парсеров
	 * @return array array of parsers
	 */
	public function getParsers()
	{
		return $this->parsers;
	}

	/**
	 * Filters all parsers that you don´t want
	 * @param  array $only chosen parsers
	 * @return array parsers
	 */
	private function arrayOnly(array $only)
	{
		return array_intersect_key($this->parsers, array_flip($only));
	}

	/**
	 * Removes the parsers that you don´t want
	 * @param  array $except parsers to exclude
	 * @return array parsers
	 */
	private function arrayExcept(array $excepts)
	{
		return array_diff_key($this->parsers, array_flip($excepts));
	}
}
