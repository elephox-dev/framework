<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;

class ArgumentTemplate extends ParameterTemplate
{
	public function __construct(
		string $name,
		public readonly bool $hasDefault = false,
		null|string|int|float|bool $default = null,
		?string $description = null,
		?Closure $validator = null,
	) {
		parent::__construct($name, $default, $description, $validator);
	}
}
