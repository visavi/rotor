<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#

/**
 * Класс компрессии страниц
 * Сжимает страницы на лету и выводит результат сжатия в процентах
 * Имеется проверка на установленную библиотеку gzip или включенную директиву zlib.output_compression
 * Также проверяется поддержка сжатия браузером у посетителей
 *
 * @author  Vantuz <visavi.net@mail.ru>
 * @copyright 2005-2013 RotorCMS, http://visavi.net
 * @version 0.1 2013-09-16
 */
class Compressor {

	/* Установка заголовков и сжатие на лету */
	public static function start()
	{
		if (extension_loaded('zlib') &&
			ini_get('zlib.output_compression') != 'On' &&
			ini_get('output_handler') != 'ob_gzhandler' &&
			ini_get('output_handler') != 'zlib.output_compression'
		)
		{
			$check_compress = self::check_compress();

			if ($check_compress == 'gzip')
			{
				header("Content-Encoding: gzip");
				ob_start(array("Compressor", "compress_output_gzip"));
			}
			elseif ($check_compress == 'deflate')
			{
				header("Content-Encoding: deflate");
				ob_start(array("Compressor", "compress_output_deflate"));
			}
		}

	}

	/* Вывод результатов сжатия */
	public static function result()
	{
		$check_compress = self::check_compress();

		if ($check_compress)
		{
			$contents = ob_get_contents();
			$gzip_file = strlen($contents);

			if ($check_compress == 'gzip')
			{
				$gzip_file_out = strlen(self::compress_output_gzip($contents));

			}
			elseif ($check_compress == 'deflate')
			{
				$gzip_file_out = strlen(self::compress_output_deflate($contents));
			}

			return $compression = round(100 - (100 / ($gzip_file / $gzip_file_out)), 1);

		}
	}

	/* Проверка поддерживает ли браузер сжатие */
	protected static function check_compress()
	{
		// Чтение заголовков
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			$gzencode = $_SERVER['HTTP_ACCEPT_ENCODING'];
		}
		elseif (isset($_SERVER['HTTP_TE']))
		{
			$gzencode = $_SERVER['HTTP_TE'];
		}
		else
		{
			$gzencode = false;
		}

		// Поиск поддержки сжатия в заголовках
		if (strpos($gzencode, 'gzip') !== false)
		{
			$support = 'gzip';
		}
		elseif (strpos($gzencode, 'deflate') !== false)
		{
			$support = 'deflate';
		}
		else
		{
			$support = false;
		}

		return $support;
	}

	/* Сжатие gzencode */
	public static function compress_output_gzip($output)
	{
		return gzencode($output, 5);
	}

	/* Сжатие gzdeflate */
	public static function compress_output_deflate($output)
	{
		return gzdeflate($output, 5);
	}

}
?>
