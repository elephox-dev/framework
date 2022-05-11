<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;

class OptionTemplate extends ParameterTemplate
{
	public function __construct(
		string $name,
		public readonly ?string $short,
		null|string|int|float|bool $default,
		?string $description = null,
		?Closure $validator = null,
	) {
		parent::__construct($name, $default, $description, $validator);
	}
}
