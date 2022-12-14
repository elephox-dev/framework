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
use Elephox\DI\Hooks\AliasHookData;
use Elephox\DI\Hooks\Contract\AliasAddedHook;
use Elephox\DI\Hooks\Contract\AliasRemovedHook;
use Elephox\DI\Hooks\Contract\ServiceAddedHook;
use Elephox\DI\Hooks\Contract\ServiceRemovedHook;
use Elephox\DI\Hooks\Contract\ServiceReplacedHook;
use Elephox\DI\Hooks\Contract\ServiceRequestedHook;
use Elephox\DI\Hooks\Contract\ServiceResolvedHook;
use Elephox\DI\Hooks\Contract\UnknownAliasRequestedHook;
use Elephox\DI\Hooks\Contract\UnknownServiceRequestedHook;
use Elephox\DI\Hooks\ServiceDescriptorHookData;
use Elephox\DI\Hooks\ServiceHookData;
use Elephox\DI\Hooks\ServiceReplacedHookData;
use Elephox\DI\Hooks\ServiceResolvedHookData;
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

	private array $hooks = [
		AliasAddedHook::class => /** @var list<AliasAddedHook> */ [],
		AliasRemovedHook::class => /** @var list<AliasRemovedHook> */ [],
		ServiceAddedHook::class => /** @var list<ServiceAddedHook> */ [],
		ServiceRemovedHook::class => /** @var list<ServiceRemovedHook> */ [],
		ServiceReplacedHook::class => /** @var list<ServiceReplacedHook> */ [],
		ServiceRequestedHook::class => /** @var list<ServiceRequestedHook> */ [],
		ServiceResolvedHook::class => /** @var list<ServiceResolvedHook> */ [],
		UnknownAliasRequestedHook::class => /** @var list<UnknownAliasRequestedHook> */ [],
		UnknownServiceRequestedHook::class => /** @var list<UnknownServiceRequestedHook> */ [],
	];

	public function __construct()
	{
		$this->services = new ArraySet(
			comparer: static fn (?ServiceDescriptor $a, ?ServiceDescriptor $b): bool => $a?->serviceType === $b?->serviceType,
		);

		/** @var ArrayMap<non-empty-string, class-string> aliases */
		$this->aliases = new ArrayMap();

		$this->registerSelf();
	}

	private function registerSelf(): void
	{
		$this->addSingleton(Contract\ServiceCollection::class, instance: $this);
		$this->addSingleton(Resolver::class, instance: $this);
	}

	protected function getServices(): ServiceCollectionContract
	{
		return $this;
	}

	public function resolver(): Resolver
	{
		return $this->requireService(Resolver::class);
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
		$replacedData = null;
		$addedData = null;

		if ($added) {
			$addedData = new ServiceDescriptorHookData($descriptor);
		} elseif ($replace) {
			unset(
				$this->descriptorCache[$descriptor->serviceType],
				$this->factoryCache[$descriptor->serviceType],
				$this->descriptorCache[$descriptor->implementationType],
				$this->factoryCache[$descriptor->implementationType],
			);

			/** @var ServiceDescriptor<TService, object> $oldDescriptor */
			$oldDescriptor = $this->services->first(static fn (ServiceDescriptor $d) => $d->serviceType === $descriptor->serviceType);

			$replacedData = new ServiceReplacedHookData($oldDescriptor, $descriptor);
			/** @var ServiceReplacedHook $replacedHook */
			foreach ($this->hooks[ServiceReplacedHook::class] as $replacedHook) {
				$replacedHook->serviceReplaced($replacedData);
			}

			if (!$replacedData->cancel) {
				$this->services->remove($replacedData->oldService);
				$this->services->add($replacedData->newService);

				$addedData = new ServiceDescriptorHookData($replacedData->newService);
			}
		}

		if ($replacedData !== null && !$replacedData->cancel) {
			$removedData = new ServiceDescriptorHookData($replacedData->oldService);
			/** @var ServiceRemovedHook $removedHook */
			foreach ($this->hooks[ServiceRemovedHook::class] as $removedHook) {
				$removedHook->serviceRemoved($removedData);
			}
		}

		if ($addedData !== null) {
			/**
			 * @var ServiceDescriptorHookData<TService> $addedData
			 * @var ServiceAddedHook $hook
			 */
			foreach ($this->hooks[ServiceAddedHook::class] as $hook) {
				$hook->serviceAdded($addedData);
			}
		}

		return $this;
	}

	public function registerHooks(object $consumer): void
	{
		$interfaces = class_implements($consumer);
		if (is_array($interfaces)) {
			foreach (array_keys($this->hooks) as $hookType) {
				if (in_array($hookType, $interfaces, true)) {
					/** @psalm-suppress MixedArrayAssignment */
					$this->hooks[$hookType][] = $consumer;
				}
			}
		}
	}

	public function describe(string $service, string $concrete, ServiceLifetime $lifetime, ?Closure $factory = null, ?object $implementation = null, bool $replace = false): Contract\ServiceCollection
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (empty($service) || empty($concrete)) {
			throw new InvalidArgumentException('Service name and implementation name must not be empty.');
		}

		if (empty($implementation) && empty($factory)) {
			$factory = fn (): object => $this->resolver()->instantiate($concrete);
		}

		return $this->add(new ServiceDescriptor($service, $concrete, $lifetime, $factory, $implementation), $replace);
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
			->where(static fn (ServiceDescriptor $sd) => $sd->implementationFactory !== null)
			->forEach(static fn (ServiceDescriptor $sd) => $sd->instance = null)
		;

		$this->services
			->where(static fn (ServiceDescriptor $sd) => $sd->implementationFactory === null)
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
		$requestedData = new ServiceHookData($serviceName, null);

		/** @var ServiceRequestedHook $hook */
		foreach ($this->hooks[ServiceRequestedHook::class] as $hook) {
			$hook->serviceRequested($requestedData);
		}

		if ($requestedData->serviceDescriptor !== null) {
			$descriptor = $requestedData->serviceDescriptor;
		} else {
			/** @psalm-suppress DocblockTypeContradiction */
			if (empty($serviceName)) {
				throw new InvalidArgumentException('Service name must not be empty.');
			}

			$descriptor = $this->tryFindDescriptor($serviceName);
			if ($descriptor === null) {
				/** @var UnknownServiceRequestedHook $hook */
				foreach ($this->hooks[UnknownServiceRequestedHook::class] as $hook) {
					$hook->unknownServiceRequested($requestedData);
				}

				if (!$requestedData->hasServiceDescriptor()) {
					throw new ServiceNotFoundException($serviceName);
				}

				/** @var ServiceDescriptor<TService, TService> $descriptor */
				$descriptor = $requestedData->serviceDescriptor;
			}
		}

		if ($descriptor->lifetime === ServiceLifetime::Singleton && $descriptor->instance !== null) {
			$resolvedData = new ServiceResolvedHookData($serviceName, $descriptor, $descriptor->instance);

			/** @var ServiceResolvedHook $hook */
			foreach ($this->hooks[ServiceResolvedHook::class] as $hook) {
				$hook->serviceResolved($resolvedData);
			}

			return $descriptor->instance;
		}

		/** @var Closure(mixed): (null|TService) $factory */
		$factory = $this->getImplementationFactory($descriptor);

		try {
			/** @var null|TService $service */
			$service = $this->callback($factory);
		} catch (BadFunctionCallException $e) {
			throw new ServiceInstantiationException($serviceName, previous: $e);
		}

		if ($service === null) {
			throw new ServiceInstantiationException($serviceName);
		}

		$resolvedData = new ServiceResolvedHookData($serviceName, $descriptor, $service);

		/** @var ServiceResolvedHook $hook */
		foreach ($this->hooks[ServiceResolvedHook::class] as $hook) {
			$hook->serviceResolved($resolvedData);
		}

		return $service;
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
			ServiceLifetime::Scoped => $this->getSingletonFactory($descriptor, true),
			ServiceLifetime::Singleton => $this->getSingletonFactory($descriptor, false),
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

	private function getSingletonFactory(ServiceDescriptor $descriptor, bool $scoped): callable
	{
		return function () use ($descriptor, $scoped): ?object {
			if ($descriptor->instance === null) {
				if ($descriptor->implementationFactory === null) {
					throw new InvalidServiceDescriptorException($scoped ? 'Scoped' : 'Singleton' . " service '$descriptor->implementationType' has no factory and no instance.");
				}

				/** @var null|object */
				$descriptor->instance = $this->callback($descriptor->implementationFactory);
			}

			return $descriptor->instance;
		};
	}

	public function hasService(string $serviceName): bool
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (empty($serviceName)) {
			throw new InvalidArgumentException('Service name must not be empty.');
		}

		return $this->services->any(static fn (ServiceDescriptor $d) => $d->serviceType === $serviceName || $d->implementationType === $serviceName);
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
			$data = new AliasHookData($alias, null);
			/** @var UnknownAliasRequestedHook $hook */
			foreach ($this->hooks[UnknownAliasRequestedHook::class] as $hook) {
				$hook->unknownAliasRequested($data);
			}

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
		} else {
			$data = new AliasHookData($alias, null);
			/** @var UnknownAliasRequestedHook $hook */
			foreach ($this->hooks[UnknownAliasRequestedHook::class] as $hook) {
				$hook->unknownAliasRequested($data);
			}

			if ($data->serviceName === null) {
				throw new ServiceAliasNotFoundException($alias);
			}

			$serviceName = $data->serviceName;
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

		$aliasData = new AliasHookData($alias, $serviceName);
		/** @var AliasAddedHook $hook */
		foreach ($this->hooks[AliasAddedHook::class] as $hook) {
			$hook->aliasAdded($aliasData);
		}

		if ($aliasData->serviceName === null) {
			throw new InvalidArgumentException('Alias hook must not set service name to null.');
		}

		$this->aliases->put($aliasData->alias, $aliasData->serviceName);

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
				$this->factoryCache[$d->implementationType],
			);

			$data = new ServiceDescriptorHookData($d);

			/** @var ServiceRemovedHook $hook */
			foreach ($this->hooks[ServiceRemovedHook::class] as $hook) {
				$hook->serviceRemoved($data);
			}

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

		$data = new AliasHookData($alias, null);

		/** @var AliasRemovedHook $hook */
		foreach ($this->hooks[AliasRemovedHook::class] as $hook) {
			$hook->aliasRemoved($data);
		}

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
