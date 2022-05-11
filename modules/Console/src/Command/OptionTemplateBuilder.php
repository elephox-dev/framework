<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;
use LogicException;

class OptionTemplateBuilder extends ParameterTemplateBuilder
{
	public function __construct(
		?string $name = null,
		private ?string $short = null,
		null|string|int|float|bool $default = null,
		?string $description = null,
		?Closure $validator = null,
	) {
		parent::__construct($name, $default, $description, $validator);
	}

	public function setShort(string $short): static
	{
		$this->short = $short;

		return $this;
	}

	public function getShort(): ?string
	{
		return $this->short;
	}

	public function build(): OptionTemplate
	{
		return new OptionTemplate(
			$this->getName() ?? throw new LogicException('Option name is required'),
			$this->short,
			$this->getDefault(),
			$this->getDescription(),
			$this->getValidator(),
		);
	}
}
