<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use LogicException;

/**
 * @property string $name
 * @property bool $hasDefault
 * @property null|string|int|float|bool $default
 * @property null|string $description
 * @property null|Closure $validator
 */
class Argument
{
	public static function fromTemplate(ArgumentTemplate $template, null|string|int|float|bool $value): self
	{
		if ($template->validator !== null) {
			$isValid = (bool) ($template->validator)($value);
			if (!$isValid) {
				throw new ArgumentValidationException($template->name);
			}
		}

		return new self(
			$template,
			$value,
		);
	}

	public function __construct(
		public readonly ArgumentTemplate $template,
		public readonly null|string|int|float|bool $value,
	) {
	}

	public function __get(string $name): mixed
	{
		return $this->template->$name;
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException('Cannot set argument value');
	}

	public function __isset(string $name): bool
	{
		return isset($this->template->$name);
	}
}
