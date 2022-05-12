<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Closure;

class ArgumentTemplate extends ParameterTemplate
{
	/**
	 * @param string $name
	 * @param bool $hasDefault
	 * @param string|int|float|bool|null $default
	 * @param string|null $description
	 * @param null|Closure(string|int|float|bool|null): (bool|string) $validator
	 */
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
