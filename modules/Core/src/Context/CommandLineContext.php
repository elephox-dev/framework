<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Collection\ArrayList;
use Elephox\Core\ActionType;
use Elephox\DI\Contract\Container as ContainerContract;
use JetBrains\PhpStorm\Pure;

class CommandLineContext extends AbstractContext implements Contract\CommandLineContext
{
	private readonly string $command;

	/** @var ArrayList<string> */
	private readonly ArrayList $args;

	/**
	 * @param ContainerContract $container
	 * @param string|list<string> $commandLine
	 */
	public function __construct(
		ContainerContract $container,
		string|array   $commandLine,
	)
	{
		parent::__construct(ActionType::Command, $container);

		$container->register(Contract\CommandLineContext::class, $this);

		if (is_string($commandLine)) {
			$this->args = ArrayList::fromArray(explode(' ', $commandLine));
		} else {
			$this->args = ArrayList::fromArray($commandLine);
		}

		if ($this->args->isEmpty()) {
			$this->command = '';
		} else {
			$this->command = $this->args->shift();
		}
	}

	#[Pure] public function getCommandLine(): string
	{
		$line = $this->command;

		if (!$this->args->isEmpty()) {
			return $line . ' ' . $this->args->join(' ');
		}

		return $line;
	}

	public function getCommand(): string
	{
		return $this->command;
	}

	/**
	 * @return ArrayList<string>
	 */
	public function getArgs(): ArrayList
	{
		return $this->args;
	}
}
