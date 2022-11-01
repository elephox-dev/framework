<?php
declare(strict_types=1);

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;

if (!function_exists('collect')) {
	function collect(mixed... $values): GenericEnumerable {
		return Enumerable::from($values);
	}
}
