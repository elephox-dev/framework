<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use JetBrains\PhpStorm\Pure;
use LogicException;

/**
 * @property string $name
 * @property null|string $description
 * @property null|string|int|float|bool $default
 * @property bool $required
 */
class Argument
{
	#[Pure]
	public static function fromTemplate(ArgumentTemplate $template, null|string|int|float|bool $value): self
	{
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
