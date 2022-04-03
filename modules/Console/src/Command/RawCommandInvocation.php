<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;
use InvalidArgumentException;

class RawCommandInvocation
{
	/**
	 * @param list<string>|null $commandLine
	 * @return RawCommandInvocation
	 */
	public static function fromCommandLine(?array $commandLine = null): RawCommandInvocation
	{
		global $argv;
		$commandLine ??= $argv;
		$raw = implode(" ", $commandLine);
		$argList = ArrayList::from($commandLine);

		if ($argList->isEmpty()) {
			throw new InvalidArgumentException("Command line is empty");
		}

		$binary = $argList->shift();

		if ($argList->isEmpty()) {
			throw new InvalidArgumentException("No command provided");
		}

		$commandName = $argList->shift();

		$compoundArgumentsKey = null;
		$compoundArgumentsValue = null;
		$compoundQuotes = null;

		/** @psalm-suppress InvalidArgument */
		return new self(
			$commandName,
			$argList->aggregate(function (CommandInvocationArgumentsMap $map, string $arg, int $index) use (&$compoundArgumentsKey, &$compoundArgumentsValue, &$compoundQuotes) {
				if (str_starts_with($arg, "--")) {
					if (str_contains($arg, "=")) {
						[$key, $value] = explode("=", $arg, 2);

						$key = trim($key, '-');
					} else {
						$key = trim($arg, '-');
						$value = true;
					}
				} else {
					$key = $index;
					$value = $arg;
				}

				if ($compoundArgumentsKey === null && is_string($value) && (str_starts_with($value, "\"") || str_starts_with($value, "'"))) {
					$compoundArgumentsKey = $key;
					$compoundArgumentsValue = substr($value, 1);
					$compoundQuotes = $value[0];
				} else if (is_string($compoundQuotes) && is_string($value) && is_string($compoundArgumentsValue) && is_string($compoundArgumentsKey) && str_ends_with($value, $compoundQuotes)) {
					$compoundArgumentsValue .= " " . substr($value, 0, -1);

					$map->put($compoundArgumentsKey, $compoundArgumentsValue);

					$compoundArgumentsKey = null;
					$compoundArgumentsValue = null;
					$compoundQuotes = null;
				} else if (is_string($compoundArgumentsValue)) {
					$compoundArgumentsValue .= " " . $value;
				} else {
					$map->put($key, $value);
				}

				return $map;
			}, new CommandInvocationArgumentsMap()),
			$binary,
			$raw,
		);
	}

	/**
	 * @param string $name
	 * @param CommandInvocationArgumentsMap $arguments
	 */
	public function __construct(
		public readonly string $name,
		public readonly CommandInvocationArgumentsMap $arguments,
		public readonly string $invokedBinary,
		public readonly string $commandLine,
	)
	{
	}

	public function build(CommandTemplate $template): CommandInvocation
	{
		return new CommandInvocation(
			$this,
			ArgumentList::create($template, $this->arguments),
		);
	}
}
