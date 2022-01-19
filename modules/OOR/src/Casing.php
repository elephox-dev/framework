<?php

namespace Elephox\OOR;

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

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function toCamel(string $string): string
	{
		/** @var string $result */
		$result = \Safe\preg_replace('/([\s\-_]+)/', '', ucwords($string, '-_ '));

		return lcfirst($result);
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function toSnake(string $string): string
	{
		/** @var string $result */
		$result = \Safe\preg_replace('/([A-Z])/', '_$1', self::toCamel($string));

		return self::toLower($result);
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function toKebab(string $string): string
	{
		/** @var string $result */
		$result = \Safe\preg_replace('/([A-Z])/', '-$1', self::toCamel($string));

		return self::toLower($result);
	}
}
