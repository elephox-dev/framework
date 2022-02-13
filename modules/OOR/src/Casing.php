<?php

namespace Elephox\OOR;

use Exception;

class Casing
{
	public static function toLower(string $string): string
	{
		return mb_strtolower($string, 'UTF-8');
	}

	public static function toUpper(string $string): string
	{
		return mb_strtoupper($string, 'UTF-8');
	}

	public static function toTitle(string $string): string
	{
		return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
	}

	public static function replaceDelimiters(string $string, string $replacement, string $delimitersPattern = '/([\s\-_]+)/'): string
	{
		/** @var string */
		return preg_replace($delimitersPattern, $replacement, $string);
	}

	public static function toCamel(string $string): string
	{
		return lcfirst(self::replaceDelimiters(self::toTitle($string), ''));
	}

	public static function toSnake(string $string): string
	{
		return self::toLower(self::replaceDelimiters(self::toTitle($string), '_'));
	}

	public static function toKebab(string $string): string
	{
		return self::toLower(self::toHttpHeader($string));
	}

	public static function toCobol(string $string): string
	{
		return self::toUpper(self::toHttpHeader($string));
	}

	public static function toHttpHeader(string $string): string
	{
		return self::replaceDelimiters(self::toTitle($string), '-');
	}

	public static function toPascal(string $string): string
	{
		return ucfirst(self::toCamel($string));
	}

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
