<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

class AliasHookData
{
	/**
	 * @param string $alias
	 * @param ServiceDescriptor|null $serviceDescriptor
	 */
	public function __construct(
		public readonly string $alias,
		public ?ServiceDescriptor $serviceDescriptor,
	) {
	}

	public function hasServiceDescriptor(): bool
	{
		return $this->serviceDescriptor !== null;
	}
}
