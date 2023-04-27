<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

interface RootServiceProvider extends ServiceProvider
{
	public function createScope(): ServiceScope;
}
