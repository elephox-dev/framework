<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

/**
 * @template TService of object
 */
class ServiceHookData
{
	/**
	 * @param class-string<TService> $serviceName
	 * @param ServiceDescriptor<TService, TService>|null $serviceDescriptor
	 */
	public function __construct(
		public readonly string $serviceName,
		public ?ServiceDescriptor $serviceDescriptor,
	) {
	}

	public function hasServiceDescriptor(): bool
	{
		return $this->serviceDescriptor !== null;
	}
}
