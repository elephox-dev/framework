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
class ServiceDescriptor
{
	/**
	 * @var null|Closure(Resolver): TImplementation $implementationFactory
	 */
	public readonly ?Closure $implementationFactory;

	/**
	 * @param class-string<TService> $serviceType
	 * @param class-string<TImplementation> $implementationType
	 * @param ServiceLifetime $lifetime
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $instance
	 */
	public function __construct(
		public readonly string $serviceType,
		public readonly string $implementationType,
		public readonly ServiceLifetime $lifetime,
		?Closure $factory,
		public ?object $instance,
	) {
		if ($factory === null && $this->instance === null) {
			throw new InvalidServiceDescriptorException('Either factory or instance must be set.');
		}

		if ($this->lifetime === ServiceLifetime::Transient && $factory === null) {
			throw new InvalidServiceDescriptorException('Transient service must have a factory set.');
		}

		if ($this->instance !== null) {
			$this->implementationFactory = null;
			self::checkInstanceImplementsType($this->instance);
		} else {
			$this->implementationFactory = static function (Resolver $resolver) use ($factory): object {
				/** @var TImplementation $instance */
				$instance = $resolver->callback($factory);

				self::checkInstanceImplementsType($instance);

				return $instance;
			};
		}
	}

	private function checkInstanceImplementsType(object $instance): void
	{
		if (!($instance instanceof $this->implementationType)) {
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
