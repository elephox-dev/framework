<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use LogicException;

/**
 * @property string $name
 * @property null|string $short
 * @property bool $hasValue
 * @property null|string|int|float|bool $default
 * @property null|string $description
 * @property null|Closure $validator
 */
class Option
{
	public static function fromTemplate(OptionTemplate $template, null|string|int|float|bool $value): self
	{
		if ($template->validator !== null) {
			$isValid = (bool) ($template->validator)($value);
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
