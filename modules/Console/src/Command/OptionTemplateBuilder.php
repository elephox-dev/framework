<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;
use LogicException;

class OptionTemplateBuilder extends ParameterTemplateBuilder
{
	/**
	 * @param string|null $name
	 * @param string|null $short
	 * @param bool $hasValue
	 * @param bool $repeated
	 * @param list<string>|string|int|float|bool|null $default
	 * @param string|null $description
	 * @param null|Closure(list<string>|string|int|float|bool|null): (bool|string) $validator
	 */
	public function __construct(
		?string $name = null,
		private ?string $short = null,
		private bool $hasValue = false,
		private bool $repeated = false,
		null|array|string|int|float|bool $default = false,
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

	/**
	 * @param list<string>|string|int|float|bool|null $default
	 *
	 * @return static
	 */
	public function setDefault(array|float|bool|int|string|null $default): static
	{
		parent::setDefault($default);
		$this->hasValue = !is_bool($default);

		return $this;
	}

	public function setIsRepeated(bool $isRepeated): static
	{
		$this->repeated = $isRepeated;

		return $this;
	}

	public function build(): OptionTemplate
	{
		return new OptionTemplate(
			$this->getName() ?? throw new LogicException('Option name is required'),
			$this->short,
			$this->hasValue,
			$this->repeated,
			$this->getDefault(),
			$this->getDescription(),
			$this->getValidator(),
		);
	}
}
