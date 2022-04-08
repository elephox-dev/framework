<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;
use InvalidArgumentException;

class CommandTemplateBuilder
{
	/**
	 * @param null|string $name
	 * @param null|ArrayList<ArgumentTemplate> $arguments
	 * @param null|string $description
	 */
	public function __construct(
		private ?string $name = null,
		private ?string $description = null,
		private ?ArrayList $arguments = null,
	) {
	}

	public function name(?string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function description(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function argument(string $name, ?string $description = null, null|string|int|float|bool $default = null, bool $required = true): self
	{
		/** @var ArrayList<ArgumentTemplate> */
		$this->arguments ??= new ArrayList();
		$this->arguments->add(new ArgumentTemplate($name, $description, $default, $required));

		return $this;
	}

	public function optional(string $name, null|string|int|float|bool $default, ?string $description = null): self
	{
		return $this->argument($name, $description, $default, false);
	}

	public function required(string $name, ?string $description = null): self
	{
		return $this->argument($name, $description);
	}

	public function build(): CommandTemplate
	{
		/** @var ArrayList<ArgumentTemplate> */
		$this->arguments ??= new ArrayList();

		return new CommandTemplate(
			$this->name ?? throw new InvalidArgumentException('Command name is required'),
			$this->description ?? '',
			$this->arguments,
		);
	}
}
