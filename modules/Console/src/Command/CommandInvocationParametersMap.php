<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayMap;
use RuntimeException;

/**
 * @extends ArrayMap<int|string, string|bool|null>
 */
class CommandInvocationParametersMap extends ArrayMap
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

	/**
	 * @param string $commandLine
	 *
	 * @return self
	 *
	 * @throws IncompleteCommandLineException
	 */
	public static function fromCommandLine(string $commandLine): self
	{
		$map = new self();
		$commandLine = trim($commandLine);

		/*
		 * States:
		 * i  = initial
		 * n  = none
		 * s  = short option
		 * sn = short option names
		 * o  = long option
		 * on = long option name
		 * ov = long option value
		 * uv = unquoted long option value
		 * qv = quoted long option value
		 * ua = unquoted argument
		 * qa = quoted argument
		 * qe = quoted value end
		 */
		$state = 'i';

		$argument = null;
		$argumentCount = 0;
		$shortOptions = null;
		$option = null;
		$optionValue = null;
		$quotation = null;

		$max = strlen($commandLine);
		$i = 0;
		while ($i < $max) {
			$char = $commandLine[$i];

			switch ($state) {
				case 'i':
				case 'n':
					if ($char === '-') {
						$state = 's';
					} elseif ($char === '/') {
						$state = 'o';
					} elseif ($char === '"') {
						$quotation = '"';
						$state = 'qa';
					} elseif ($char === "'") {
						$quotation = "'";
						$state = 'qa';
					} else {
						$argument = $char;
						$state = 'ua';
					}

					break;
				case 's':
					if ($char === '-') {
						$state = 'o';
					} else {
						$shortOptions = $char;
						$state = 'sn';
					}

					break;
				case 'sn':
					/** @var non-empty-string $shortOptions */
					$shortOptionCount = strlen($shortOptions);
					if ($char === '=') {
						$option = $shortOptions[$shortOptionCount - 1];
						for ($j = 0; $j < $shortOptionCount - 1; $j++) {
							$map->put($shortOptions[$j], true);
						}

						$shortOptions = null;
						$state = 'ov';
					} elseif ($char === ' ') {
						for ($j = 0; $j < $shortOptionCount; $j++) {
							$map->put($shortOptions[$j], true);
						}

						$shortOptions = null;
						$state = 'n';
					} else {
						$shortOptions .= $char;
					}

					break;
				case 'o':
					if ($char === ' ') {
						$option = '--';
						$optionValue = substr($commandLine, $i);
						$state = 'uv';

						break 2;
					}

					$option = $char;
					$state = 'on';

					break;
				case 'on':
					/** @var non-empty-string $option */
					if ($char === '=') {
						$state = 'ov';
					} elseif ($char === ' ') {
						$map->put($option, true);
						$state = 'n';
					} else {
						$option .= $char;
					}

					break;
				case 'ov':
					if ($char === '"') {
						$quotation = '"';
						$state = 'qv';
					} elseif ($char === "'") {
						$quotation = "'";
						$state = 'qv';
					} else {
						$optionValue = $char;
						$state = 'uv';
					}

					break;
				case 'uv':
					/**
					 * @var non-empty-string $option
					 * @var string $optionValue
					 */
					if ($char === ' ') {
						$map->put($option, $optionValue);
						$state = 'n';
					} else {
						$optionValue .= $char;
					}

					break;
				case 'qv':
					/**
					 * @var non-empty-string $option
					 * @var string $optionValue
					 */
					if ($char === $quotation) {
						$map->put($option, $optionValue);
						$state = 'qe';
					} else {
						$optionValue .= $char;
					}

					break;
				case 'ua':
					/** @var string $argument */
					if ($char === ' ') {
						$map->put($argumentCount, $argument);
						$argumentCount++;
						$state = 'n';
					} else {
						$argument .= $char;
					}

					break;
				case 'qa':
					/** @var string $argument */
					if ($char === $quotation) {
						$map->put($argumentCount, $argument);
						$argumentCount++;
						$state = 'qe';
					} else {
						$argument .= $char;
					}

					break;
				case 'qe':
					if ($char === ' ') {
						$state = 'n';
					} else {
						throw new IncompleteCommandLineException('Additional characters after quoted argument');
					}

					break;
				default:
					/** @var string $state */
					throw new RuntimeException("Unknown state: $state");
			}

			$i++;
		}

		switch ($state) {
			case 'i':
			case 'n':
			case 'qe':
				break;
			case 'ua':
				/** @var string $argument */
				$map->put($argumentCount, $argument);

				break;
			case 'sn':
				/** @var non-empty-string $shortOptions */
				for ($j = 0, $shortOptionCount = strlen($shortOptions); $j < $shortOptionCount; $j++) {
					$map->put($shortOptions[$j], true);
				}

				break;
			case 'on':
				/** @var string $option */
				$map->put($option, true);

				break;
			case 'uv':
				/**
				 * @var non-empty-string $option
				 * @var string $optionValue
				 */
				$map->put($option, $optionValue);

				break;
			case 'ov':
				/** @var non-empty-string $option */
				$map->put($option, null);

				break;
			case 's':
				throw new IncompleteCommandLineException('Expected short option identifier');
			case 'o':
				throw new IncompleteCommandLineException('Expected long option identifier');
			case 'qv':
			case 'qa':
				/** @var string $quotation */
				throw new IncompleteCommandLineException('Expected second quote (' . $quotation . ') to end quoted argument');
		}

		return $map;
	}
}
