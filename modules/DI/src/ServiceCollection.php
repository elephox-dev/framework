<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use Closure;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\ArraySet;
use Elephox\DI\Contract\Resolver;
use InvalidArgumentException;

/**
 * @psalm-type service-object = object
 */
class ServiceCollection implements Contract\ServiceCollection
{
	/**
	 * @var ArraySet<ServiceDescriptor> $services
	 */
	private readonly ArraySet $services;

	/** @var ArrayMap<non-empty-string, class-string> $aliases */
	private readonly ArrayMap $aliases;

	protected readonly Resolver $resolver;

	/** @var array<class-string, ServiceDescriptor> */
	private array $descriptorCache = [];

	/** @var array<class-string, Closure> */
	private array $factoryCache = [];

	public function __construct(?Resolver $resolver = null)
	{
		$this->services = new ArraySet(
			comparer: fn(ServiceDescriptor $a, ServiceDescriptor $b): bool => $a->serviceType === $b->serviceType
		);

		/** @var ArrayMap<non-empty-string, class-string> aliases */
		$this->aliases = new ArrayMap();

		$this->resolver = $resolver ?? new AutoResolver($this);

		$this->addSingleton(Contract\ServiceCollection::class, implementation: $this);
		$this->addSingleton(Resolver::class, implementation: $this->resolver);
	}

	/**
	 * @template TService of service-object
	 * @template TImplementation of service-object
	 *
	 * @param ServiceDescriptor<TService, TImplementation> $descriptor
	 *
	 * @return Contract\ServiceCollection
	 */
	protected function add(ServiceDescriptor $descriptor): Contract\ServiceCollection
	{
		$this->services->add($descriptor);

		return $this;
	}

	public function describe(string $serviceName, string $implementationName, ServiceLifetime $lifetime, ?Closure $implementationFactory = null, ?object $implementation = null): Contract\ServiceCollection
	{
		if (empty($serviceName) || empty($implementationName)) {
			throw new InvalidArgumentException('Service name and implementation name must not be empty.');
		}

		return $this->add(new ServiceDescriptor($serviceName, $implementationName, $lifetime, $implementationFactory, $implementation));
	}

	public function addTransient(string $serviceName, string $implementationName, ?Closure $implementationFactory = null, ?object $implementation = null): Contract\ServiceCollection
	{
		return $this->describe($serviceName, $implementationName, ServiceLifetime::Transient, $implementationFactory, $implementation);
	}

	public function addSingleton(string $serviceName, ?string $implementationName = null, ?Closure $implementationFactory = null, ?object $implementation = null): Contract\ServiceCollection
	{
		if ($implementationName === null && $implementation === null) {
			throw new InvalidArgumentException('Either implementation name and factory or an implementation must be provided.');
		}

		if ($implementationName === null) {
			$implementationName = $implementation::class;
		}

		return $this->describe($serviceName, $implementationName, ServiceLifetime::Singleton, $implementationFactory, $implementation);
	}

	/**
	 * @template TService of service-object
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
	 * @template TService of service-object
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
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		$descriptor = $this->tryFindDescriptor($serviceName);
		if ($descriptor === null) {
			throw new ServiceNotFoundException($serviceName);
		}

		/** @var Closure(mixed): TService $factory */
		$factory = $this->getImplementationFactory($descriptor);

		try {
			return $this->resolver->callback($factory);
		} catch (BadFunctionCallException $e) {
			throw new ServiceInstantiationException($serviceName, previous: $e);
		}
	}

	/**
	 * @template TService of service-object
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
		$descriptor = $this->services->firstOrDefault(null, static fn(ServiceDescriptor $d) => $d->serviceType === $serviceName || $d->implementationType === $serviceName);
		if ($descriptor === null) {
			return null;
		}

		$this->descriptorCache[$serviceName] = $descriptor;

		return $descriptor;
	}

	/**
	 * @template TService of service-object
	 *
	 * @param ServiceDescriptor<TService, TService> $descriptor
	 *
	 * @return callable(mixed): TService
	 */
	private function getImplementationFactory(ServiceDescriptor $descriptor): callable
	{
		if (array_key_exists($descriptor->implementationType, $this->factoryCache)) {
			return $this->factoryCache[$descriptor->serviceType];
		}

		/** @var Closure(mixed): TService $factory */
		$factory = match ($descriptor->lifetime) {
			ServiceLifetime::Transient => $this->getTransientFactory($descriptor),
			ServiceLifetime::Singleton => $this->getSingletonFactory($descriptor),
		};

		$this->factoryCache[$descriptor->serviceType] = $factory;

		return $factory;
	}

	private function getTransientFactory(ServiceDescriptor $descriptor): callable
	{
		if ($descriptor->implementationFactory === null) {
			throw new InvalidServiceDescriptorException("Transient service '$descriptor->implementationType' must have an implementation factory.");
		}

		return $descriptor->implementationFactory;
	}

	private function getSingletonFactory(ServiceDescriptor $descriptor): callable
	{
		return function() use ($descriptor): object {
			if ($descriptor->instance === null) {
				if ($descriptor->implementationFactory === null) {
					throw new InvalidServiceDescriptorException("Singleton service '$descriptor->implementationType' has no factory and no instance.");
				}

				$descriptor->instance = $this->resolver->callback($descriptor->implementationFactory);
			}

			return $descriptor->instance;
		};
	}

	public function hasService(string $serviceName): bool
	{
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		return $this->services->any(static fn (ServiceDescriptor $d) => $d->serviceType === $serviceName || $d->implementationType === $serviceName);
	}

	/**
	 * @template TService of service-object
	 *
	 * @param string $alias
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function getByAlias(string $alias): ?object
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
		}

		/** @var class-string<TService> $serviceName */
		$serviceName = $this->aliases->get($alias);
		if ($serviceName === null) {
			return null;
		}

		return $this->getService($serviceName);
	}

	/**
	 * @template TService of service-object
	 *
	 * @param string $alias
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no service with the given alias exists
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function requireByAlias(string $alias): object
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
		}

		if (!$this->hasAlias($alias)) {
			throw new ServiceAliasNotFoundException($alias);
		}

		/** @var class-string<TService> $serviceName */
		$serviceName = $this->aliases->get($alias);
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
	 * @template TService of service-object
	 *
	 * @param string $aliasOrServiceName
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function get(string $aliasOrServiceName): ?object
	{
		if (empty($aliasOrServiceName)) {
			throw new InvalidArgumentException('Alias or service name must not be empty.');
		}

		if ($this->hasAlias($aliasOrServiceName)) {
			return $this->getByAlias($aliasOrServiceName);
		}

		/** @var class-string<TService> $aliasOrServiceName */
		return $this->getService($aliasOrServiceName);
	}

	/**
	 * @template TService of service-object
	 *
	 * @param string $aliasOrServiceName
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no service with the given alias exists
	 * @throws InvalidArgumentException if the alias is empty
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
		if (empty($alias) || empty($serviceName)) {
			throw new InvalidArgumentException("Alias and service name must not be empty.");
		}

		$this->aliases->put($alias, $serviceName);

		return $this;
	}

	public function has(string $aliasOrServiceName): bool
	{
		if (empty($aliasOrServiceName)) {
			throw new InvalidArgumentException('Alias or service name must not be empty.');
		}

		/** @var class-string $aliasOrServiceName */
		return $this->hasAlias($aliasOrServiceName) || $this->hasService($aliasOrServiceName);
	}

	public function removeService(string $serviceName): Contract\ServiceCollection
	{
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		$this->services->removeBy(static fn (ServiceDescriptor $d) => $d->serviceType === $serviceName);

		return $this;
	}

	public function removeAlias(string $alias): Contract\ServiceCollection
	{
		if (empty($alias)) {
			throw new InvalidArgumentException('Alias must not be empty.');
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
}
