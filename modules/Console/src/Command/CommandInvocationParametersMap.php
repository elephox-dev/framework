<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayMap;
use JsonException;
use RuntimeException;

/**
 * @extends ArrayMap<int|string, list<string>|int|string|bool|null>
 */
class CommandInvocationParametersMap extends ArrayMap
{
	public const INVALID_OPTION_NAME_CHARS = [' ', '=', '"', "'"];

	/**
	 * @param string $commandLine
	 *
	 * @return self
	 *
	 * @throws IncompleteCommandLineException
	 * @throws JsonException
	 */
	public static function fromCommandLine(string $commandLine): self
	{
		$map = new self();
		$commandLine = trim($commandLine);

		/*
		 * States:
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
		$state = 'n';

		$argument = null;
		$argumentCount = 0;
		$shortOptions = null;
		$option = null;
		$optionValue = null;
		$quotation = null;

		$addShortOptionsToMap = static function (string $opts) use ($map): void {
			for ($j = 0, $optsCount = strlen($opts); $j < $optsCount; $j++) {
				$opt = $opts[$j];
				if ($map->has($opt)) {
					$old = $map->get($opt);
					if (is_bool($old)) {
						$value = 2;
					} elseif (is_int($old)) {
						$value = $old + 1;
					} else {
						trigger_error(sprintf('Option "%s" was already defined with value %s. Repeated option reset this to "true"', $opt, json_encode($old, JSON_THROW_ON_ERROR)), E_USER_WARNING);

						$value = true;
					}
				} else {
					$value = true;
				}

				$map->put($opt, $value);
			}
		};

		$addOptionToMap = static function (string $name, string $value) use ($map): void {
			if ($map->has($name)) {
				$old = $map->get($name);
				if (is_array($old)) {
					$old[] = $value;
					$mapValue = $old;
				} else {
					$mapValue = [$old, $value];
				}
			} else {
				$mapValue = $value;
			}

			$map->put($name, $mapValue);
		};

		$max = strlen($commandLine);
		$i = 0;
		while ($i < $max) {
			$char = $commandLine[$i];

			switch ($state) {
				case 'n':
					if ($char === '-') {
						$state = 's';
					} elseif ($char === '/') {
						$state = 'o';
					} elseif ($char === '"' || $char === "'") {
						$quotation = $char;
						$state = 'qa';
						$argument = '';
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
						$addShortOptionsToMap(substr($shortOptions, 0, -1));

						$shortOptions = null;
						$state = 'ov';
					} elseif ($char === ' ') {
						$addShortOptionsToMap($shortOptions);

						$shortOptions = null;
						$state = 'n';
					} else {
						$shortOptions .= $char;
					}

					break;
				case 'o':
					if (in_array($char, self::INVALID_OPTION_NAME_CHARS, true)) {
						throw new InvalidCommandLineException("Invalid option name character: '$char'");
					}

					$option = $char;
					$state = 'on';

					break;
				case 'on':
					/** @var non-empty-string $option */
					if ($char === '=') {
						$state = 'ov';
					} elseif ($char === ' ') {
						if ($map->has($option)) {
							trigger_error(sprintf('Option "%s" was already defined with value %s. Repeated option reset this to "true"', $option, json_encode($map->get($option), JSON_THROW_ON_ERROR)), E_USER_WARNING);
						}

						$map->put($option, true);
						$state = 'n';
					} else {
						if (in_array($char, self::INVALID_OPTION_NAME_CHARS, true)) {
							throw new InvalidCommandLineException("Invalid option name character: '$char'");
						}

						$option .= $char;
					}

					break;
				case 'ov':
					if ($char === '"' || $char === "'") {
						$quotation = $char;
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
						$addOptionToMap($option, $optionValue);
						$optionValue = null;

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
						$addOptionToMap($option, $optionValue ?? '');
						$optionValue = null;

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
						throw new InvalidCommandLineException("Additional character after quoted argument: '$char'");
					}

					break;
			}

			$i++;
		}

		switch ($state) {
			case 'n':
			case 'qe':
				break;
			case 'ua':
				assert(is_string($argument));

				$map->put($argumentCount, $argument);

				break;
			case 'sn':
				assert(is_string($shortOptions));

				$addShortOptionsToMap($shortOptions);

				break;
			case 'on':
				assert(is_string($option));
				if ($map->has($option) && !is_bool($map->get($option))) {
					trigger_error(sprintf('Option "%s" was already defined with value %s. Repeated option reset this to "true"', $option, json_encode($map->get($option), JSON_THROW_ON_ERROR)), E_USER_WARNING);
				}

				$map->put($option, true);

				break;
			case 'uv':
				assert(is_string($option) && $option !== '');
				assert(is_string($optionValue));

				$addOptionToMap($option, $optionValue);

				break;
			case 'ov':
				assert(is_string($option) && $option !== '');

				$map->put($option, null);

				break;
			case 's':
				throw new IncompleteCommandLineException('Expected short option identifier');
			case 'o':
				throw new IncompleteCommandLineException('Expected long option identifier');
			case 'qv':
			case 'qa':
				assert(is_string($quotation));

				throw new IncompleteCommandLineException('Expected second quote (' . $quotation . ') to end quoted argument');
		}

		return $map;
	}
}
