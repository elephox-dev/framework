<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use Elephox\DI\Contract\Resolver;

/**
 * TODO: Update TImplementation to extend TService once vimeo/psalm#7795 is resolved.
 *
 * @psalm-type service-object = object
 *
 * @template TService of service-object
 * @template TImplementation of service-object
 */
readonly class ServiceDescriptor
{
	/**
	 * @param class-string<TService> $serviceType
	 * @param null|class-string<TImplementation> $implementationType
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param null|TImplementation $instance
	 */
	public function __construct(
		public string $serviceType,
		public ?string $implementationType,
		public ServiceLifetime $lifetime,
		private ?Closure $factory,
		private ?object $instance,
	) {
		if ($this->factory === null && $this->instance === null) {
			throw new InvalidServiceDescriptorException('Either factory or instance must be set.');
		}

		if ($this->lifetime !== ServiceLifetime::Singleton && $this->factory === null) {
			throw new InvalidServiceDescriptorException('Transient/scoped services must have a factory set.');
		}
	}

	/**
	 * @param Resolver $resolver
	 *
	 * @return TImplementation
	 */
	public function createInstance(Resolver $resolver): object
	{
		if ($this->factory !== null) {
			/** @var TImplementation $instance */
			$instance = $resolver->call($this->factory);
		} elseif ($this->instance !== null) {
			$instance = $this->instance;
		} else {
			throw new InvalidServiceDescriptorException('Either factory or instance must be set.');
		}

		$this->checkInstanceImplementsType($instance);

		/** @var TImplementation $instance */
		return $instance;
	}

	private function checkInstanceImplementsType(object $instance): void
	{
		if ($this->implementationType !== null && !($instance instanceof $this->implementationType)) {
			throw new InvalidServiceDescriptorException(sprintf(
				'Instance must be of given implementation type (%s). Given instance is of type %s.',
				$this->implementationType,
				get_debug_type($instance),
			));
		}

		if (str_contains($this->serviceType, '&')) {
			$types = explode('&', $this->serviceType);
			foreach ($types as $subType) {
				if (!$instance instanceof $subType) {
					throw new InvalidServiceDescriptorException(sprintf(
						'Instance must be an intersection of all service types (%s), but the type %s is missing the %s type.',
						$this->serviceType,
						get_debug_type($instance),
						$subType,
					));
				}
			}
		} elseif (!$instance instanceof $this->serviceType) {
			throw new InvalidServiceDescriptorException(sprintf(
				'Instance must be of given service type (%s). Given instance is of type %s.',
				$this->serviceType,
				get_debug_type($instance),
			));
		}
	}
}
