<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

interface ServiceScopeFactory
{
	public function createScope(): ServiceScope;
}
