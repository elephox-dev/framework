<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;

abstract class ParameterTemplate
{
	public function __construct(
		public readonly string $name,
		public readonly null|string|int|float|bool $default,
		public readonly ?string $description,
		public readonly ?Closure $validator,
	) {
	}
}
