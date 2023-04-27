<?php
declare(strict_types=1);

namespace Elephox\DI;

use Elephox\DI\Contract\ScopedServiceProvider;
use LogicException;

class ServiceScope implements Contract\ServiceScope
{
	private bool $closed = false;

	public function __construct(
		private readonly ScopedServiceProvider $serviceProvider,
	) {
	}

	public function endScope(): void
	{
		$this->closed = true;
		$this->serviceProvider->dispose();
	}

	public function services(): ScopedServiceProvider
	{
		if ($this->closed) {
			throw new LogicException('ServiceScope is already closed');
		}

		return $this->serviceProvider;
	}
}
