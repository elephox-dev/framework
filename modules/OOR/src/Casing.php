<?php
declare(strict_types=1);

namespace Elephox\OOR;

use Exception;

class Casing
{
	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "hello beautiful world"
	 */
	public static function toLower(string $string): string
	{
		return mb_strtolower($string, 'UTF-8');
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HELLO BEAUTIFUL WORLD"
	 */
	public static function toUpper(string $string): string
	{
		return mb_strtoupper($string, 'UTF-8');
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "Hello Beautiful World"
	 */
	public static function toTitle(string $string): string
	{
		return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
	}

	/**
	 * Example input: "Hello beautiful World", "-"<br>
	 * Example output: "Hello-beautiful-World"
	 */
	public static function replaceDelimiters(string $string, string $replacement, string $delimitersPattern = '/([\s\-_]+)/'): string
	{
		/** @var string */
		return preg_replace($delimitersPattern, $replacement, $string);
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "helloBeautifulWorld"
	 */
	public static function toCamel(string $string): string
	{
		return lcfirst(self::replaceDelimiters(self::toTitle($string), ''));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "hello_beautiful_world"
	 */
	public static function toSnake(string $string): string
	{
		return self::toLower(self::replaceDelimiters(self::toTitle($string), '_'));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "hello-beautiful-world"
	 */
	public static function toKebab(string $string): string
	{
		return self::toLower(self::toHttpHeader($string));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HELLO-BEAUTIFUL-WORLD"
	 */
	public static function toCobol(string $string): string
	{
		return self::toUpper(self::toHttpHeader($string));
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HelloBeautifulWorld"
	 */
	public static function toHttpHeader(string $string): string
	{
		return self::replaceDelimiters(self::toTitle($string), '-');
	}

	/**
	 * Example input: "Hello beautiful World"<br>
	 * Example output: "HelloBeautifulWorld"
	 */
	public static function toPascal(string $string): string
	{
		return ucfirst(self::replaceDelimiters(self::toTitle($string), ''));
	}

	/**
	 * Changes all characters randomly to lower or upper case using the current timestamp as a seed.
	 * You can also pass your own seed for reproducible results.
	 *
	 * Example input: "Hello beautiful World", 1<br>
	 * Example output: "heLLo beaUTiFul WoRLD"
	 *
	 * @param ?int $seed
	 */
	public static function random(string $string, ?int $seed = null): string
	{
		try {
			mt_srand($seed ?? time());

			for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
				$string[$i] = mt_rand(0, 1) === 0 ? strtoupper($string[$i]) : strtolower($string[$i]);
			}

			return $string;
		} catch (Exception) {
			return $string;
		}
	}
}
