<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;
use JsonException;

readonly class RawCommandInvocation
{
	/**
	 * @param array<int, string> $commandLineArgs
	 *
	 * @throws EmptyCommandLineException
	 * @throws NoCommandInCommandLineException
	 * @throws IncompleteCommandLineException
	 * @throws JsonException
	 */
	public static function fromCommandLine(array $commandLineArgs): self
	{
		$argList = ArrayList::from($commandLineArgs)->select(static fn (string $a) => str_contains($a, ' ') ? "\"$a\"" : $a)->toArrayList();
		$raw = $argList->aggregate(static fn (string $line, string $arg): string => $line . ' ' . $arg, '');

		if ($argList->isEmpty()) {
			throw new EmptyCommandLineException();
		}

		$binary = $argList->shift();

		if ($argList->isEmpty()) {
			throw new NoCommandInCommandLineException();
		}

		$commandName = $argList->shift();

		return new self(
			$commandName,
			CommandInvocationParametersMap::fromCommandLine($argList->implode(' ')),
			$binary,
			$raw,
		);
	}

	public function __construct(
		public string $name,
		public CommandInvocationParametersMap $parameters,
		public string $invokedBinary,
		public string $commandLine,
	) {
	}

	public function apply(CommandTemplate $template): CommandInvocation
	{
		return new CommandInvocation(
			$this,
			ArgumentList::create($template, $this->parameters),
			OptionList::create($template, $this->parameters),
		);
	}
}
