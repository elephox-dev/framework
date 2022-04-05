<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;
use InvalidArgumentException;

class RawCommandInvocation
{
	/**
	 * @param array<int, string> $commandLineArgs
	 * @return RawCommandInvocation
	 */
	public static function fromCommandLine(array $commandLineArgs): RawCommandInvocation
	{
		$raw = implode(" ", $commandLineArgs);
		$argList = ArrayList::from($commandLineArgs);

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
			CommandInvocationArgumentsMap::fromArgs($argList),
			$binary,
			$raw,
		);
	}

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
