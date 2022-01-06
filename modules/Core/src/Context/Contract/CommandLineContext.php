<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

use Elephox\Collection\Contract\ReadonlyList;

interface CommandLineContext extends Context
{
	public function getCommandLine(): string;

	public function getCommand(): string;

	/**
	 * @return ReadonlyList<string>
	 */
	public function getArgs(): ReadonlyList;
}
