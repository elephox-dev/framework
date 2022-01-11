<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

use Elephox\Collection\Contract\GenericList;

interface CommandLineContext extends Context
{
	public function getCommandLine(): string;

	public function getCommand(): string;

	/**
	 * @return GenericList<string>
	 */
	public function getArgs(): GenericList;
}
