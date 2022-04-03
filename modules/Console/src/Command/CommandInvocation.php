<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use InvalidArgumentException;
use LogicException;

/**
 * @property-read string $name
 * @property-read string $invokedBinary
 * @property-read string $commandLine
 */
class CommandInvocation
{
	public function __construct(
		public RawCommandInvocation $raw,
		public ArgumentList $arguments,
	)
	{
	}

	public function getArgument(string $name): Argument
	{
		return $this->getOptionalArgument($name) ?? throw new InvalidArgumentException("Argument with name \"$name\" not found.");
	}

	public function getOptionalArgument(string $name): ?Argument
	{
		return $this->arguments
				->firstOrDefault(
					null,
					static fn(Argument $arg): bool => $arg->name === $name
				);
	}

	public function __get(string $name): mixed
	{
		return $this->raw->$name ?? $this->getArgument($name)->value;
	}

	public function __isset(string $name): bool
	{
		return isset($this->raw->$name) || $this->arguments->any(static fn(Argument $arg): bool => $arg->name === $name);
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException('Cannot set argument value');
	}
}
