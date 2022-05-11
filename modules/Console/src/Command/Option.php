<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use LogicException;

/**
 * @property-read string $name
 * @property-read null|string $short
 * @property-read bool $hasValue
 * @property-read null|string|int|float|bool $default
 * @property-read null|string $description
 * @property-read null|Closure $validator
 */
class Option
{
	public static function fromTemplate(OptionTemplate $template, null|string|int|float|bool $value): self
	{
		if ($template->validator !== null) {
			$isValid = (bool)($template->validator)($value);
			if (!$isValid) {
				throw new OptionValidationException($template->name);
			}
		}

		return new self(
			$template,
			$value,
		);
	}

	public function __construct(
		public readonly OptionTemplate $template,
		public readonly null|string|int|float|bool $value,
	) {
	}

	public function __get(string $name): mixed
	{
		return $this->template->$name;
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException('Cannot set option value');
	}

	public function __isset(string $name): bool
	{
		return isset($this->template->$name);
	}
}
