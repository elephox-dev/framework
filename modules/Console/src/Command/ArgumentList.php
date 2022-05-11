<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayMap;
use LogicException;

/**
 * @extends ArrayMap<string|int, Argument>
 */
class ArgumentList extends ArrayMap
{
	public static function create(CommandTemplate $template, CommandInvocationParametersMap $argumentsMap): self
	{
		$arguments = new self();

		$argCount = 0;
		foreach ($argumentsMap->whereKey(static fn ($k) => is_numeric($k)) as $value) {
			$match = $template->argumentTemplates->firstOrDefault(null, static fn (ArgumentTemplate $t) => $arguments->where(static fn (Argument $a) => $a->name === $t->name)->isEmpty());
			if ($match === null) {
				break;
			}

			$arg = Argument::fromTemplate($match, $value);
			$arguments->put($argCount, $arg);
			$arguments->put($match->name, $arg);
			$argCount++;
		}

		$missingArguments = $template->argumentTemplates->where(static fn (ArgumentTemplate $t) => $arguments->where(static fn (Argument $a) => $a->name === $t->name)->isEmpty())->toList();
		if (!empty($missingArguments)) {
			foreach ($missingArguments as $missingArgument) {
				if (!$missingArgument->hasDefault) {
					throw new RequiredArgumentMissingException($missingArgument->name);
				}

				$arg = Argument::fromTemplate($missingArgument, $missingArgument->default);
				$arguments->put($argCount, $arg);
				$arguments->put($template->name, $arg);
				$argCount++;
			}
		}

		return $arguments;
	}

	public function tryGet(string $name): ?Argument
	{
		return $this->firstOrDefault(null, static fn(Argument $a) => $a->name === $name);
	}

	public function get(mixed $key): Argument
	{
		if (is_int($key)) {
			return parent::get($key);
		}

		return $this->tryGet($key) ?? throw new ArgumentNotFoundException("Argument '$key' not found.");
	}

	public function __get(string $name): Argument
	{
		return $this->get($name);
	}

	public function __isset(string $name): bool
	{
		return $this->tryGet($name) !== null;
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException("Cannot set arguments.");
	}
}
