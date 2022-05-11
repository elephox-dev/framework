<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;

class OptionTemplate extends ParameterTemplate
{
	public function __construct(
		string $name,
		public readonly ?string $short = null,
		public readonly bool $hasValue = false,
		null|string|int|float|bool $default = null,
		?string $description = null,
		?Closure $validator = null,
	) {
		parent::__construct($name, $default, $description, $validator);
	}
}
