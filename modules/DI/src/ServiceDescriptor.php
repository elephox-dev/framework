<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\Serializers\Native;

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
		public readonly string $lifetime,
		public $implementationFactory,
		public ?object $instance
	)
	{
		if ($this->implementationFactory === null && $this->instance === null) {
			throw new InvalidServiceDescriptorException('Either implementationFactory or instance must be set.');
		}

		if ($this->lifetime === ServiceLifetime::Transient->value && $this->implementationFactory === null) {
			throw new InvalidServiceDescriptorException('Transient service must have implementationFactory set.');
		}
	}

	public function __serialize(): array
	{
		$data = [
			'serviceType' => $this->serviceType,
			'implementationType' => $this->implementationType,
			'lifetime' => $this->lifetime,
		];

		if ($this->implementationFactory !== null) {
			$data['implementationFactory'] = $this->implementationFactory instanceof Native ? $this->implementationFactory : new SerializableClosure($this->implementationFactory);
		} else {
			$data['implementationFactory'] = null;
		}

		if ($this->instance !== null) {
			$data['instance'] = $this->instance;
		} else {
			$data['instance'] = null;
		}

		return $data;
	}

	public function __unserialize(array $data): void
	{
		$this->serviceType = $data['serviceType'];
		$this->implementationType = $data['implementationType'];
		$this->lifetime = $data['lifetime'];
		$this->implementationFactory = $data['implementationFactory']?->getClosure();
		$this->instance = $data['instance'];
	}
}
