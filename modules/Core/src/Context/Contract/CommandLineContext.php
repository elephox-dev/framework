<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

interface CommandLineContext extends Context
{
	public function getCommand(): ?string;
	public function getArgs(): array;
}
