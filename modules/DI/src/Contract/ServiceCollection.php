<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InvalidServiceDescriptorException;
use Elephox\DI\ServiceInstantiationException;
use Elephox\DI\ServiceLifetime;
use Elephox\DI\ServiceNotFoundException;
use InvalidArgumentException;

/**
 * @psalm-type service-object = object
 */
interface ServiceCollection
{
	public function resolver(): Resolver;

	/**
	 * @template TService of service-object
	 * @template TImplementation of service-object
	 *
	 * @param class-string<TService> $serviceName
	 * @param class-string<TImplementation> $implementationName
	 * @param ServiceLifetime $lifetime
	 * @param null|Closure(mixed): TImplementation $implementationFactory
	 * @param TImplementation|null $implementation
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function describe(string $serviceName, string $implementationName, ServiceLifetime $lifetime, ?Closure $implementationFactory = null, ?object $implementation = null): ServiceCollection;

	/**
	 * @template TService of service-object
	 * @template TImplementation of service-object
	 *
	 * @param class-string<TService> $serviceName
	 * @param class-string<TImplementation> $implementationName
	 * @param Closure(mixed): TImplementation $implementationFactory
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 */
	public function addTransient(string $serviceName, string $implementationName, Closure $implementationFactory): ServiceCollection;

	/**
	 * @template TService of service-object
	 * @template TImplementation of service-object
	 *
	 * @param class-string<TService> $serviceName
	 * @param null|class-string<TImplementation> $implementationName
	 * @param null|Closure(mixed): TImplementation $implementationFactory
	 * @param TImplementation|null $implementation
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function addSingleton(string $serviceName, ?string $implementationName = null, ?Closure $implementationFactory = null, ?object $implementation = null): ServiceCollection;

	/**
	 * @template TService of service-object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @return TService|null
	 */
	public function getService(string $serviceName): ?object;

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
	public function requireService(string $serviceName): object;

	/**
	 * @template TService of service-object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function hasService(string $serviceName): bool;

	/**
	 * @template TService of service-object
	 *
	 * @param string $alias
	 * @param class-string<TService> $serviceName
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the alias or the service name is empty
	 */
	public function setAlias(string $alias, string $serviceName): ServiceCollection;

	/**
	 * @template TService of service-object
	 *
	 * @param string $alias
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function getByAlias(string $alias): ?object;

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
	public function requireByAlias(string $alias): object;

	/**
	 * @param string $alias
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function hasAlias(string $alias): bool;

	/**
	 * @param string $aliasOrServiceName
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function has(string $aliasOrServiceName): bool;

	/**
	 * @template TService of service-object
	 *
	 * @param string $aliasOrServiceName
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function get(string $aliasOrServiceName): ?object;

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
	public function require(string $aliasOrServiceName): object;

	/**
	 * @param string $serviceName
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function removeService(string $serviceName): ServiceCollection;

	/**
	 * @param string $alias
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function removeAlias(string $alias): ServiceCollection;

	/**
	 * @param string $aliasOrServiceName
	 *
	 * @return ServiceCollection
	 *
	 * @throws InvalidArgumentException if the alias or service name is empty
	 */
	public function remove(string $aliasOrServiceName): ServiceCollection;
}
