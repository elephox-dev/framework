<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<int|string, string|bool>
 */
class CommandInvocationArgumentsMap extends ArrayMap
{
	/**
	 * @param iterable<int, string> $args
	 */
	public static function fromArgs(iterable $args): self
	{
		$map = new self();

		$compoundArgumentsKey = null;
		$compoundArgumentsValue = null;
		$compoundQuotes = null;
		$nonNamedIndex = 0;

		foreach ($args as $arg) {
			if (str_starts_with($arg, '--')) {
				if (str_contains($arg, '=')) {
					[$key, $value] = explode('=', $arg, 2);

					$key = trim($key, '-');
				} else {
					$key = trim($arg, '-');
					$value = true;
				}
			} else {
				$key = $nonNamedIndex;
				$value = $arg;

				$nonNamedIndex++;
			}

			if ($compoundArgumentsKey === null && is_string($value) && (str_starts_with($value, '"') || str_starts_with($value, "'"))) {
				$compoundArgumentsKey = $key;
				$compoundArgumentsValue = substr($value, 1);
				$compoundQuotes = $value[0];
			} elseif (is_string($compoundQuotes) && is_string($value) && is_string($compoundArgumentsValue) && is_string($compoundArgumentsKey) && str_ends_with($value, $compoundQuotes)) {
				$compoundArgumentsValue .= ' ' . substr($value, 0, -1);

				$map->put($compoundArgumentsKey, $compoundArgumentsValue);

				$compoundArgumentsKey = null;
				$compoundArgumentsValue = null;
				$compoundQuotes = null;
			} elseif (is_string($compoundArgumentsValue)) {
				$compoundArgumentsValue .= ' ' . $value;
			} else {
				$map->put($key, $value);
			}
		}

		return $map;
	}
}
