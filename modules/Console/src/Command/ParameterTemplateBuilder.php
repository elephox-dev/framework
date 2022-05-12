<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;

abstract class ParameterTemplateBuilder
{
	/**
	 * @param string|null $name
	 * @param string|int|float|bool|null $default
	 * @param string|null $description
	 * @param null|Closure(string|int|float|bool|null): (bool|string) $validator
	 */
	public function __construct(
		private ?string $name,
		private null|string|int|float|bool $default,
		private ?string $description,
		private ?Closure $validator,
	) {
	}

	public function setName(string $name): static
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setDefault(null|string|int|float|bool $default): static
	{
		$this->default = $default;

		return $this;
	}

	public function getDefault(): null|string|int|float|bool
	{
		return $this->default;
	}

	public function setDescription(string $description): static
	{
		$this->description = $description;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param callable(string|int|float|bool|null): (bool|string) $validator
	 *
	 * @return static
	 */
	public function setValidator(callable $validator): static
	{
		$this->validator = $validator(...);

		return $this;
	}

	/**
	 * @return null|Closure(string|int|float|bool|null): (string|bool) $validator
	 */
	public function getValidator(): ?Closure
	{
		return $this->validator;
	}

	abstract public function build(): ParameterTemplate;
}
