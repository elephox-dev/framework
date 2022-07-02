<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

/**
 * @template TService of object
 */
class ServiceResolvedHookData
{
	/**
	 * @param class-string<TService> $serviceName
	 * @param ServiceDescriptor<TService, TService> $serviceDescriptor
	 * @param TService $service
	 */
	public function __construct(
		public readonly string $serviceName,
		public readonly ServiceDescriptor $serviceDescriptor,
		public readonly object $service,
	) {
	}
}
