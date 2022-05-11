<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;
use Elephox\Collection\ArrayList;
use InvalidArgumentException;

class CommandTemplateBuilder
{
	/**
	 * @param null|string $name
	 * @param null|string $description
	 * @param null|ArrayList<ArgumentTemplateBuilder> $arguments
	 * @param null|ArrayList<OptionTemplateBuilder> $options
	 */
	public function __construct(
		private ?string $name = null,
		private ?string $description = null,
		private ?ArrayList $arguments = null,
		private ?ArrayList $options = null,
	) {
	}

	public function setName(?string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function addArgument(string $name, bool $hasDefault = false, null|string|int|float|bool $default = null, ?string $description = null, ?callable $validator = null): ArgumentTemplateBuilder
	{
		/** @var ArrayList<ArgumentTemplateBuilder> */
		$this->arguments ??= new ArrayList();

		if ($this->hasArgument($name)) {
			throw new InvalidArgumentException("Argument with name '$name' already exists.");
		}

		$builder = new ArgumentTemplateBuilder($name, $hasDefault, $default, $description, $validator !== null ? $validator(...) : null);
		$this->arguments->add($builder);

		return $builder;
	}

	public function hasArgument(string $name): bool
	{
		return $this->arguments?->any(static fn (ArgumentTemplateBuilder $argument) => $argument->getName() === $name) ?? false;
	}

	public function addOption(string $name, ?string $short = null, bool $hasValue = false, null|string|int|float|bool $default = null, ?string $description = null, ?callable $validator = null): OptionTemplateBuilder
	{
		/** @var ArrayList<OptionTemplateBuilder> */
		$this->options ??= new ArrayList();

		if ($this->hasOption($name)) {
			throw new InvalidArgumentException(sprintf('Option with name "%s" already exists.', $name));
		}

		if ($short !== null && $this->hasShortOption($short)) {
			throw new InvalidArgumentException(sprintf('Option with short "%s" already exists.', $name));
		}

		$builder = new OptionTemplateBuilder($name, $short, $hasValue, $default, $description, $validator !== null ? $validator(...) : null);
		$this->options->add($builder);

		return $builder;
	}

	public function hasOption(string $name): bool
	{
		return $this->options?->any(static fn (OptionTemplateBuilder $option) => $option->getName() === $name) ?? false;
	}

	public function hasShortOption(string $short): bool
	{
		return $this->options?->any(static fn (OptionTemplateBuilder $option) => $option->getShort() === $short) ?? false;
	}

	public function build(): CommandTemplate
	{
		/** @var ArrayList<ArgumentTemplateBuilder> */
		$this->arguments ??= new ArrayList();

		/** @var ArrayList<OptionTemplateBuilder> */
		$this->options ??= new ArrayList();

		return new CommandTemplate(
			$this->name ?? throw new InvalidArgumentException('Command name is required'),
			$this->description ?? '',
			$this->arguments->select(static fn ($b) => $b->build()),
			$this->options->select(static fn ($b) => $b->build()),
		);
	}
}
