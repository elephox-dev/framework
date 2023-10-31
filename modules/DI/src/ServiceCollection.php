<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use Elephox\Collection\IsEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use InvalidArgumentException;
use Traversable;

readonly class ServiceCollection implements ServiceCollectionContract
{
	use IsEnumerable;

	private ObjectSet $descriptors;

	public function __construct()
	{
		$this->descriptors = new ObjectSet();
	}

	public function getIterator(): Traversable
	{
		return $this->descriptors->getIterator();
	}

	public function add(ServiceDescriptor $descriptor): self
	{
		$this->descriptors->add($descriptor);

		return $this;
	}

	private function has(ServiceDescriptor $descriptor): bool
	{
		return $this->hasService($descriptor->serviceType);
	}

	private function hasService(string $service): bool
	{
		return $this->descriptors->any(static fn ($d) => $d->serviceType === $service);
	}

	public function tryAdd(ServiceDescriptor $descriptor): self
	{
		if ($this->has($descriptor)) {
			return $this;
		}

		return $this->add($descriptor);
	}

	public function remove(string $service): self
	{
		$this->descriptors->remove($service);

		return $this;
	}

	public function removeAll(): self
	{
		$this->descriptors->removeAll();

		return $this;
	}

	public function buildProvider(): ServiceProvider
	{
		return new ServiceProvider($this->descriptors);
	}

	/**
	 * @param null|class-string $implementationType
	 * @param null|Closure(mixed): object $factory
	 */
	protected function describe(string $service, ServiceLifetime $lifetime, ?string $implementationType = null, ?Closure $factory = null, ?object $implementation = null): ServiceDescriptor
	{
		if ($service === '') {
			throw new InvalidArgumentException('Service name name must not be empty.');
		}

		/**
		 * @var class-string $service
		 * @var null|class-string $implementationType
		 */
		if ($implementation === null && $factory === null) {
			if (!class_exists($service) && $implementationType === null) {
				throw new InvalidArgumentException('Either one of implementationType, implementation or factory must be set if the service is not a class name.');
			}

			$implementationType ??= $service;

			$factory = static function (Resolver $resolver) use ($implementationType): object {
				/** @var object */
				return $resolver->instantiate($implementationType);
			};
		}

		return new ServiceDescriptor($service, $implementationType, $lifetime, $factory, $implementation);
	}

	public function addTransient(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self
	{
		return $this->add(
			$this->describe(
				$service,
				ServiceLifetime::Transient,
				$concrete,
				$factory,
				$instance,
			),
		);
	}

	public function addScoped(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self
	{
		return $this->add(
			$this->describe(
				$service,
				ServiceLifetime::Scoped,
				$concrete,
				$factory,
				$instance,
			),
		);
	}

	public function addSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self
	{
		return $this->add(
			$this->describe(
				$service,
				ServiceLifetime::Singleton,
				$concrete,
				$factory,
				$instance,
			),
		);
	}

	public function tryAddSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): ServiceCollectionContract
	{
		if ($this->hasService($service)) {
			return $this;
		}

		return $this->addSingleton($service, $concrete, $factory, $instance);
	}

	public function tryAddTransient(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): ServiceCollectionContract
	{
		if ($this->hasService($service)) {
			return $this;
		}

		return $this->addTransient($service, $concrete, $factory, $instance);
	}

	public function tryAddScoped(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): ServiceCollectionContract
	{
		if ($this->hasService($service)) {
			return $this;
		}

		return $this->addScoped($service, $concrete, $factory, $instance);
	}
}
