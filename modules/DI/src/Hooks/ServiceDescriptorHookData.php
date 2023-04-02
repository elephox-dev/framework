<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

/**
 * @template TService of object
 */
readonly class ServiceDescriptorHookData
{
	/**
	 * @param ServiceDescriptor<TService, object> $serviceDescriptor
	 */
	public function __construct(
		public ServiceDescriptor $serviceDescriptor,
	) {
	}
}
