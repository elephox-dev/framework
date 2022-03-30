<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;
use InvalidArgumentException;

class Command
{
	/**
	 * @param string $name
	 * @param ArrayList<Argument> $arguments
	 */
	public function __construct(
		public readonly string $name,
		public readonly ArrayList $arguments,
	)
	{
	}

	public function getArgument(string $name): Argument
	{
		return $this->arguments
				->firstOrDefault(
					null,
					static fn (Argument $a): bool => $a->name === $name
				) ?? throw new InvalidArgumentException("Argument with name \"$name\" not found.");
	}
}
