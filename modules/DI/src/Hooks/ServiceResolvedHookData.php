<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

/**
 * @template TService of object
 */
readonly class ServiceResolvedHookData
{
	/**
	 * @param class-string<TService> $serviceName
	 * @param ServiceDescriptor<TService, TService> $serviceDescriptor
	 * @param TService $service
	 */
	public function __construct(
		public string $serviceName,
		public ServiceDescriptor $serviceDescriptor,
		public object $service,
	) {
	}
}
