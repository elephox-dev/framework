<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Contract\ServiceProvider;
use LogicException;

class ServiceScope implements Contract\ServiceScope
{
	public function __construct(
		private readonly ServiceProvider $serviceProvider,
	) {
	}

	public function endScope(): void
	{
		$this->serviceProvider->dispose();
	}

	public function services(): ServiceProvider
	{
		return $this->serviceProvider;
	}
}
