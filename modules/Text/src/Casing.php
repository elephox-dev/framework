<?php

namespace Elephox\Text;

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

	public static function toCamel(string $string): string
	{
		return lcfirst(\Safe\preg_replace('/([\s\-_]+)/', '', ucwords($string, '-_ ')));
	}

	public static function toSnake(string $string): string
	{
		return self::toLower(\Safe\preg_replace('/([A-Z])/', '_$1', self::toCamel($string)));
	}

	public static function toKebab(string $string): string
	{
		return self::toLower(\Safe\preg_replace('/([A-Z])/', '-$1', self::toCamel($string)));
	}
}
