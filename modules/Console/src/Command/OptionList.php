<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayMap;
use LogicException;

/**
 * @extends ArrayMap<string, Option>
 */
class OptionList extends ArrayMap
{
	public static function create(CommandTemplate $template, CommandInvocationParametersMap $argumentsMap): self
	{
		$options = new self();

		foreach ($template->optionTemplates as $optionTemplate) {
			/** @psalm-suppress UnusedClosureParam */
			$matchedPair = $argumentsMap->firstPairOrDefault(null, static fn (mixed $value, string|int $key) => $key === $optionTemplate->name || $key === $optionTemplate->short);
			if ($matchedPair === null) {
				if (!$optionTemplate->hasValue) {
					$value = false;
				} else {
					$value = $optionTemplate->default;
				}
			} else {
				$value = $matchedPair->getValue();
			}

			if ($optionTemplate->repeated) {
				$value = is_array($value) ? $value : [$value];
			} elseif (is_array($value)) {
				throw new IncompleteCommandLineException("Option '$optionTemplate->name' cannot be repeated. If you want to provide an array as a default value, use the repeated option or implode() your values to a string.");
			}

			$option = Option::fromTemplate($optionTemplate, $value);

			$options->put($optionTemplate->name, $option);
			if ($optionTemplate->short !== null) {
				$options->put($optionTemplate->short, $option);
			}
		}

		return $options;
	}

	public function tryGet(string $name): ?Option
	{
		return $this->firstOrDefault(null, static fn (Option $o) => $o->name === $name || $o->short === $name);
	}

	public function get(mixed $key): Option
	{
		return $this->tryGet($key) ?? throw new OptionNotFoundException("Option '$key' not found.");
	}

	public function __get(string $name): Option
	{
		return $this->get($name);
	}

	public function __isset(string $name): bool
	{
		return $this->tryGet($name) !== null;
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException('Cannot set options.');
	}
}
