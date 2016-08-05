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
 * Class Compression pages
 * Compress the page on the fly and outputs the result as a percentage of compression
 * There is a check on the installed library gzip or included directive zlib.output_compression
 * Compression support also checked the visitors browser
 *
 * @license Code and contributions have MIT License
 * @link    http://visavi.net
 * @author  Alexander Grigorev <visavi.net@mail.ru>
 * @version 1.0
 */
class Compressor {

	/**
	 * Compression level (0-9)
	 * @var integer
	 */
	public static $level = 5;

	/**
	 * Setting headers and compression on the fly
	 */
	public static function start()
	{
		if (extension_loaded('zlib') &&
			ini_get('zlib.output_compression') != 'On' &&
			ini_get('output_handler') != 'ob_gzhandler' &&
			ini_get('output_handler') != 'zlib.output_compression'
		) {
			$check_compress = self::checkCompress();

			if ($check_compress == 'gzip') {
				header("Content-Encoding: gzip");
				ob_start(array('self', 'compressGzip'));
			}
			elseif ($check_compress == 'x-gzip') {
				header("Content-Encoding: x-gzip");
				ob_start(array('self', 'compressXGzip'));
			}
			elseif ($check_compress == 'deflate') {
				header("Content-Encoding: deflate");
				ob_start(array('self', 'compressDeflate'));
			}
		}
	}

	/**
	 * Output of compression
	 * @return float result of the compression percentage
	 */
	public static function result()
	{
		$check_compress = self::checkCompress();

		if ($check_compress) {

			$contents = ob_get_contents();
			$size = strlen($contents);

			if ($check_compress == 'gzip')
				$size_compress = strlen(self::compressGzip($contents));
			elseif ($check_compress == 'x-gzip')
				$size_compress = strlen(self::compressXGzip($contents));
			elseif ($check_compress == 'deflate')
				$size_compress = strlen(self::compressDeflate($contents));

			return $size > $size_compress ? round(100 - 100 / ($size / $size_compress), 1) : 0;
		}
	}

	/**
	 * Check if the browser supports compression
	 * @return boolean compression is supported
	 */
	protected static function checkCompress()
	{
		// Reading the headlines
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		elseif (isset($_SERVER['HTTP_TE']))
			$encoding = $_SERVER['HTTP_TE'];
		else
			$encoding = false;

		// Search support compression titles
		if (strpos($encoding, 'gzip') !== false)
			$support = 'gzip';
		elseif (strpos($encoding, 'x-gzip') !== false)
			$support = 'x-gzip';
		elseif (strpos($encoding, 'deflate') !== false)
			$support = 'deflate';
		else
			$support = false;

		return $support;
	}

	/**
	 * Compression gzencode
	 * @param  string $output Data compression.
	 * @return mixed          The compressed string or false if an error occurs
	 */
	protected static function compressGzip($output)
	{
		return gzencode($output, self::$level);
	}

	/**
	 * Compression gzcompress
	 * @param  string $output Data compression.
	 * @return mixed          The compressed string or false if an error occurs
	 */
	protected static function compressXGzip($output)
	{
		return gzcompress($output, self::$level, ZLIB_ENCODING_GZIP);
	}

	/**
	 * Compression gzdeflate
	 * @param  [type] $output [description]
	 * @return mixed          The compressed string or false if an error occurs
	 */
	protected static function compressDeflate($output)
	{
		return gzdeflate($output, self::$level);
	}
}
