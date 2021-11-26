<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\Handler\ActionType;
use Elephox\DI\Contract\Container;

class CommandLineContext extends AbstractContext implements Contract\CommandLineContext
{
	private readonly ?string $command;
	private readonly array $args;

	public function __construct(
		Container $container,
		private string $commandLine,
	)
	{
		parent::__construct(ActionType::Command, $container);

		$container->register(Contract\CommandLineContext::class, $this);

		$parts = explode(' ', $commandLine);
		$this->command = array_shift($parts);
		$this->args = $parts;
	}

	public function getCommandLine(): string
	{
		return $this->commandLine;
	}

	public function getCommand(): ?string
	{
		return $this->command;
	}

	public function getArgs(): array
	{
		return $this->args;
	}
}
