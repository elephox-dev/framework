<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;
use LogicException;

class ArgumentTemplateBuilder extends ParameterTemplateBuilder
{
	public function __construct(
		?string $name = null,
		private bool $hasDefault = false,
		null|string|int|float|bool $default = null,
		?string $description = null,
		?Closure $validator = null,
	) {
		parent::__construct($name, $default, $description, $validator);
	}

	public function setDefault(null|string|int|float|bool $default): static
	{
		parent::setDefault($default);
		$this->hasDefault = true;

		return $this;
	}

	public function removeDefault(): static
	{
		parent::setDefault(null);
		$this->hasDefault = false;

		return $this;
	}

	public function hasDefault(): bool
	{
		return $this->hasDefault;
	}

	public function build(): ArgumentTemplate
	{
		return new ArgumentTemplate(
			$this->getName() ?? throw new LogicException('Argument name is required'),
			$this->hasDefault,
			$this->getDefault(),
			$this->getDescription(),
			$this->getValidator(),
		);
	}
}
