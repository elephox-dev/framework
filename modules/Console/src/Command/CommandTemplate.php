<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\Contract\GenericKeyedEnumerable;

readonly class CommandTemplate
{
	/**
	 * @param string $name
	 * @param string $description
	 * @param GenericKeyedEnumerable<int, ArgumentTemplate> $argumentTemplates
	 * @param GenericKeyedEnumerable<int, OptionTemplate> $optionTemplates
	 */
	public function __construct(
		public string $name,
		public string $description,
		public GenericKeyedEnumerable $argumentTemplates,
		public GenericKeyedEnumerable $optionTemplates,
	) {
	}
}
