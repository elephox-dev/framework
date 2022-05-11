<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\Contract\GenericKeyedEnumerable;

class CommandTemplate
{
	/**
	 * @param string $name
	 * @param string $description
	 * @param GenericKeyedEnumerable<int, ArgumentTemplate> $argumentTemplates
	 * @param GenericKeyedEnumerable<int, OptionTemplate> $optionTemplates
	 */
	public function __construct(
		public readonly string $name,
		public readonly string $description,
		public readonly GenericKeyedEnumerable $argumentTemplates,
		public readonly GenericKeyedEnumerable $optionTemplates,
	) {
	}
}
