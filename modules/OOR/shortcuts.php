<?php
declare(strict_types=1);

use Elephox\OOR\Arr;
use Elephox\OOR\Str;
use JetBrains\PhpStorm\Pure;

if (!function_exists('arr')) {
	#[Pure]
	function arr(mixed ...$values): Arr {
		return Arr::wrap(...$values);
	}
}

if (!function_exists('str')) {
	#[Pure]
	function str(string|Stringable|Str $string): Str {
		return Str::wrap($string);
	}
}
