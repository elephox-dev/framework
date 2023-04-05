<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use Closure;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\ArraySet;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use InvalidArgumentException;

class ServiceCollection implements Contract\ServiceCollection, Contract\Resolver
{
	use ServiceResolver;

	/**
	 * @var ArraySet<ServiceDescriptor> $services
	 */
	private readonly ArraySet $services;

	/**
	 * @var ArrayMap<non-empty-string, class-string> $aliases
	 */
	private readonly ArrayMap $aliases;

	/**
	 * @var array<class-string, ServiceDescriptor>
	 */
	private array $descriptorCache = [];

	/**
	 * @var array<class-string, Closure>
	 */
	private array $factoryCache = [];

	public function __construct()
	{
		$this->services = new ArraySet(
			comparer: static fn (?ServiceDescriptor $a, ?ServiceDescriptor $b): bool => $a?->serviceType === $b?->serviceType,
		);

		/** @var ArrayMap<non-empty-string, class-string> */
		$this->aliases = new ArrayMap();
	}

	protected function getServices(): ServiceCollectionContract
	{
		return $this;
	}

	public function resolver(): Resolver
	{
		return $this;
	}

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param ServiceDescriptor<TService, TImplementation> $descriptor
	 * @param bool $replace
	 */
	protected function add(ServiceDescriptor $descriptor, bool $replace): Contract\ServiceCollection
	{
		$added = $this->services->add($descriptor);

		if (!$added && $replace) {
			unset(
				$this->descriptorCache[$descriptor->serviceType],
				$this->factoryCache[$descriptor->serviceType],
				$this->descriptorCache[$descriptor->implementationType],
			);

			/** @var ServiceDescriptor<TService, object> $oldDescriptor */
			$oldDescriptor = $this->services->first(static fn (ServiceDescriptor $d) => $d->serviceType === $descriptor->serviceType);

			$this->services->remove($oldDescriptor);
			$this->services->add($descriptor);
		}

		return $this;
	}

	public function describe(string $service, string $concrete, ServiceLifetime $lifetime, ?Closure $factory = null, ?object $implementation = null, bool $replace = false): Contract\ServiceCollection
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if ($service === '' || $concrete === '') {
			throw new InvalidArgumentException('Service name and implementation name must not be empty.');
		}

		/**
		 * @var class-string $service
		 * @var class-string $concrete
		 */
		if ($implementation === null && $factory === null) {
			$factory = function () use ($concrete): object {
				/**
				 * @var object
				 */
				return $this->resolver()->instantiate($concrete);
			};
		}

		$descriptor = new ServiceDescriptor($service, $concrete, $lifetime, $factory, $implementation);

		return $this->add($descriptor, $replace);
	}

	public function addTransient(string $service, string $concrete, ?Closure $factory = null, ?object $instance = null, bool $replace = false): Contract\ServiceCollection
	{
		return $this->describe($service, $concrete, ServiceLifetime::Transient, $factory, $instance, $replace);
	}

	public function addScoped(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null, bool $replace = false): Contract\ServiceCollection
	{
		if ($concrete === null && $instance === null) {
			if (class_exists($service)) {
				$concrete = $service;
			} else {
				throw new InvalidArgumentException('Either implementation name and factory or an implementation must be provided.');
			}
		}

		$concrete ??= $instance::class;

		return $this->describe($service, $concrete, ServiceLifetime::Scoped, $factory, $instance, $replace);
	}

	public function addSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null, bool $replace = false): Contract\ServiceCollection
	{
		if ($concrete === null && $instance === null) {
			if (class_exists($service)) {
				$concrete = $service;
			} else {
				throw new InvalidArgumentException('Either implementation name and factory or an implementation must be provided.');
			}
		}

		$concrete ??= $instance::class;

		return $this->describe($service, $concrete, ServiceLifetime::Singleton, $factory, $instance, $replace);
	}

	public function endScope(): void
	{
		$this->services
			->where(static fn (ServiceDescriptor $sd) => $sd->lifetime === ServiceLifetime::Scoped && $sd->implementationFactory !== null)
			->forEach(static fn (ServiceDescriptor $sd) => $sd->instance = null)
		;

		$this->services
			->where(static fn (ServiceDescriptor $sd) => $sd->lifetime === ServiceLifetime::Scoped && $sd->implementationFactory === null)
			->forEach(fn (ServiceDescriptor $sd) => $this->removeService($sd->serviceType))
		;
	}

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @return TService|null
	 */
	public function getService(string $serviceName): ?object
	{
		try {
			return $this->requireService($serviceName);
		} catch (ServiceException) {
			return null;
		}
	}

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no such service is registered
	 * @throws ServiceInstantiationException if the service cannot be instantiated
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function requireService(string $serviceName): object
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		if (
			$serviceName === ServiceCollectionContract::class ||
			$serviceName === Resolver::class ||
			$serviceName === self::class
		) {
			/** @var TService */
			return $this;
		}

		$descriptor = $this->tryFindDescriptor($serviceName);
		if ($descriptor === null) {
			throw new ServiceNotFoundException($serviceName);
		}

		if ($descriptor->lifetime === ServiceLifetime::Singleton && $descriptor->instance !== null) {
			return $descriptor->instance;
		}

		/** @var Closure(Resolver): TService $factory */
		$factory = $this->getImplementationFactory($descriptor);

		try {
			/** @var TService */
			return $factory($this);
		} catch (BadFunctionCallException $e) {
			throw new ServiceInstantiationException($serviceName, previous: $e);
		}
	}

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @return ServiceDescriptor<TService, TService>|null
	 */
	private function tryFindDescriptor(string $serviceName): ?ServiceDescriptor
	{
		if (array_key_exists($serviceName, $this->descriptorCache)) {
			/** @var ServiceDescriptor<TService, TService> */
			return $this->descriptorCache[$serviceName];
		}

		/** @var ServiceDescriptor<TService, TService>|null $descriptor */
		$descriptor = $this->services->firstOrDefault(null, static fn (ServiceDescriptor $d) => $d->serviceType === $serviceName || $d->implementationType === $serviceName);
		if ($descriptor === null) {
			return null;
		}

		$this->descriptorCache[$descriptor->serviceType] = $descriptor;
		$this->descriptorCache[$descriptor->implementationType] = $descriptor;

		return $descriptor;
	}

	/**
	 * @template TService of object
	 *
	 * @param ServiceDescriptor<TService, TService> $descriptor
	 *
	 * @return Closure(Resolver): TService
	 */
	private function getImplementationFactory(ServiceDescriptor $descriptor): callable
	{
		if (array_key_exists($descriptor->serviceType, $this->factoryCache)) {
			/** @var Closure(Resolver): TService */
			return $this->factoryCache[$descriptor->serviceType];
		}

		/** @var Closure(Resolver): TService $factory */
		$factory = match ($descriptor->lifetime) {
			ServiceLifetime::Transient => $this->getTransientFactory($descriptor),
			ServiceLifetime::Scoped,
			ServiceLifetime::Singleton => $this->getSingletonFactory($descriptor),
		};

		$this->factoryCache[$descriptor->serviceType] = $factory;

		return $factory;
	}

	private function getTransientFactory(ServiceDescriptor $descriptor): callable
	{
		assert($descriptor->implementationFactory !== null, "Transient service '$descriptor->implementationType' must have an implementation factory.");

		return $descriptor->implementationFactory;
	}

	private function getSingletonFactory(ServiceDescriptor $descriptor): callable
	{
		return static function (Resolver $resolver) use ($descriptor): object {
			if ($descriptor->instance === null) {
				assert($descriptor->implementationFactory !== null, "Service '$descriptor->implementationType' has no factory and no instance.");

				$descriptor->instance = ($descriptor->implementationFactory)($resolver);
			}

			assert($descriptor->instance !== null, "Service factory for '$descriptor->implementationType' did not return an instance.");

			return $descriptor->instance;
		};
	}

	public function hasService(string $serviceName): bool
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		return $serviceName === ServiceCollectionContract::class ||
			$serviceName === Resolver::class ||
			$serviceName === self::class ||
			$this->services->any(static fn (ServiceDescriptor $d) => $d->serviceType === $serviceName || $d->implementationType === $serviceName);
	}

	/**
	 * @template TService of object
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $alias
	 */
	public function getByAlias(string $alias): ?object
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
		}

		try {
			/** @var class-string<TService> $serviceName */
			$serviceName = $this->aliases->get($alias);
		} catch (OffsetNotFoundException) {
			$serviceName = null;
		}

		if ($serviceName === null) {
			return null;
		}

		return $this->getService($serviceName);
	}

	/**
	 * @template TService of object
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no service with the given alias exists
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $alias
	 */
	public function requireByAlias(string $alias): object
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
		}

		if ($this->hasAlias($alias)) {
			$serviceName = $this->aliases->get($alias);
		}

		/** @var class-string<TService> $serviceName */
		return $this->requireService($serviceName);
	}

	public function hasAlias(string $alias): bool
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
		}

		return $this->aliases->has($alias);
	}

	/**
	 * @template TService of object
	 *
	 * @param string $id
	 *
	 *@return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function get(string $id): ?object
	{
		if (empty($id)) {
			throw new InvalidArgumentException('Alias or service name must not be empty.');
		}

		if ($this->hasAlias($id)) {
			return $this->getByAlias($id);
		}

		/** @var class-string<TService> $id */
		return $this->getService($id);
	}

	/**
	 * @template TService of object
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no service with the given alias exists
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $aliasOrServiceName
	 */
	public function require(string $aliasOrServiceName): object
	{
		if (empty($aliasOrServiceName)) {
			throw new InvalidArgumentException('Alias or service name must not be empty.');
		}

		if ($this->hasAlias($aliasOrServiceName)) {
			$aliasOrServiceName = $this->aliases->get($aliasOrServiceName);
		}

		/** @var class-string<TService> $aliasOrServiceName */
		return $this->requireService($aliasOrServiceName);
	}

	public function setAlias(string $alias, string $serviceName): Contract\ServiceCollection
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (empty($alias) || empty($serviceName)) {
			throw new InvalidArgumentException('Alias and service name must not be empty.');
		}

		$this->aliases->put($alias, $serviceName);

		return $this;
	}

	public function has(string $id): bool
	{
		if (empty($id)) {
			throw new InvalidArgumentException('Alias or service name must not be empty.');
		}

		/** @var class-string $id */
		return $this->hasAlias($id) || $this->hasService($id);
	}

	public function removeService(string $serviceName): Contract\ServiceCollection
	{
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		$this->services->removeBy(function (ServiceDescriptor $d) use ($serviceName) {
			if ($d->serviceType !== $serviceName && $d->implementationType !== $serviceName) {
				return false;
			}

			unset(
				$this->descriptorCache[$d->serviceType],
				$this->descriptorCache[$d->implementationType],
				$this->factoryCache[$d->serviceType],
			);

			return true;
		});

		return $this;
	}

	public function removeAlias(string $alias): Contract\ServiceCollection
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
		}

		if (!$this->hasAlias($alias)) {
			return $this;
		}

		$this->aliases->remove($alias);

		return $this;
	}

	public function remove(string $aliasOrServiceName): Contract\ServiceCollection
	{
		if (empty($aliasOrServiceName)) {
			throw new InvalidArgumentException('Alias or service name must not be empty.');
		}

		$this->removeAlias($aliasOrServiceName);
		$this->removeService($aliasOrServiceName);

		return $this;
	}

	public function requireServiceLate(string $serviceName): callable
	{
		return fn () => $this->requireService($serviceName);
	}

	public function requireLate(string $aliasOrServiceName): callable
	{
		return fn () => $this->require($aliasOrServiceName);
	}
}
