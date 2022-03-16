<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;

/**
 * Update TImplementation to extend TService once vimeo/psalm#7795 is resolved.
 *
 * @template TService of object
 * @template TImplementation of object
 */
class ServiceDescriptor
{
	/**
	 * @param class-string<TService> $serviceType
	 * @param class-string<TImplementation> $implementationType
	 * @param ServiceLifetime $lifetime
	 * @param null|Closure(Contract\ServiceProvider): TImplementation $implementationFactory
	 * @param TImplementation|null $instance
	 */
	public function __construct(
		public readonly string $serviceType,
		public readonly string $implementationType,
		public readonly ServiceLifetime $lifetime,
		public readonly ?Closure $implementationFactory,
		public readonly ?object $instance
	)
	{
	}
}
