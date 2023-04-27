<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

interface ServiceScope
{
	public function endScope(): void;

	public function services(): ScopedServiceProvider;
}
