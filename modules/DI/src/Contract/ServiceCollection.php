<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InvalidServiceDescriptorException;
use Elephox\DI\ServiceInstantiationException;
use Elephox\DI\ServiceLifetime;
use Elephox\DI\ServiceNotFoundException;
use InvalidArgumentException;

interface ServiceCollection
{
	public function resolver(): Resolver;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $implementation
	 * @param ServiceLifetime $lifetime
	 * @param bool $replace
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function describe(string $service, string $concrete, ServiceLifetime $lifetime, ?Closure $factory = null, ?object $implementation = null, bool $replace = false): ServiceCollection;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param class-string<TImplementation> $concrete
	 * @param Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $implementation
	 * @param bool $replace
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 */
	public function addTransient(string $service, string $concrete, Closure $factory, ?object $implementation = null, bool $replace = false): ServiceCollection;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $implementation
	 * @param bool $replace
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function addSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $implementation = null, bool $replace = false): ServiceCollection;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @return TService|null
	 */
	public function getService(string $serviceName): ?object;

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
	public function requireService(string $serviceName): object;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $serviceName
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function hasService(string $serviceName): bool;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $serviceName
	 * @param string $alias
	 *
	 * @throws InvalidArgumentException if the alias or the service name is empty
	 */
	public function setAlias(string $alias, string $serviceName): ServiceCollection;

	/**
	 * @template TService of object
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $alias
	 */
	public function getByAlias(string $alias): ?object;

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
	public function requireByAlias(string $alias): object;

	/**
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $alias
	 */
	public function hasAlias(string $alias): bool;

	/**
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $aliasOrServiceName
	 */
	public function has(string $aliasOrServiceName): bool;

	/**
	 * @template TService of object
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $aliasOrServiceName
	 */
	public function get(string $aliasOrServiceName): ?object;

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
	public function require(string $aliasOrServiceName): object;

	/**
	 * @throws InvalidArgumentException if the service name is empty
	 *
	 * @param string $serviceName
	 */
	public function removeService(string $serviceName): ServiceCollection;

	/**
	 * @throws InvalidArgumentException if the alias is empty
	 *
	 * @param string $alias
	 */
	public function removeAlias(string $alias): ServiceCollection;

	/**
	 * @throws InvalidArgumentException if the alias or service name is empty
	 *
	 * @param string $aliasOrServiceName
	 */
	public function remove(string $aliasOrServiceName): ServiceCollection;
}
