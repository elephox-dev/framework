<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

class ArgumentTemplate
{
	public function __construct(
		public readonly string $name,
		public readonly ?string $description,
		public readonly null|string|int|float|bool $default = null,
		public readonly bool $required = false,
	) {
	}
}
