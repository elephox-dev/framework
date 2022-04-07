<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;

/**
 * Update TImplementation to extend TService once vimeo/psalm#7795 is resolved.
 *
 * @psalm-type service-object = object
 *
 * @template TService of service-object
 * @template TImplementation of service-object
 */
class ServiceDescriptor
{
	/**
	 * @param class-string<TService> $serviceType
	 * @param class-string<TImplementation> $implementationType
	 * @param ServiceLifetime $lifetime
	 * @param null|Closure(mixed): TImplementation $implementationFactory
	 * @param TImplementation|null $instance
	 */
	public function __construct(
		public readonly string $serviceType,
		public readonly string $implementationType,
		public ServiceLifetime $lifetime,
		public ?Closure $implementationFactory,
		public ?object $instance,
	) {
		if ($this->implementationFactory === null && $this->instance === null) {
			throw new InvalidServiceDescriptorException('Either implementationFactory or instance must be set.');
		}

		if ($this->lifetime === ServiceLifetime::Transient && $this->implementationFactory === null) {
			throw new InvalidServiceDescriptorException('Transient service must have implementationFactory set.');
		}
	}
}
