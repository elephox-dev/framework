<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;

class CommandTemplate
{
	/**
	 * @param string $name
	 * @param ArrayList<ArgumentTemplate> $argumentTemplates
	 * @param publicreadonlystring $description
	 */
	public function __construct(
		public readonly string $name,
		public readonly string $description,
		public readonly ArrayList $argumentTemplates,
	) {
	}
}
