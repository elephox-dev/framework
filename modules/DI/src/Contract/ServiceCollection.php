<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InvalidServiceDescriptorException;
use Elephox\DI\ServiceInstantiationException;
use Elephox\DI\ServiceLifetime;
use Elephox\DI\ServiceNotFoundException;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

interface ServiceCollection extends ContainerInterface
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
	public function describe(string $service, string $concrete, ServiceLifetime $lifetime, ?Closure $factory = null, ?object $implementation = null, bool $replace = false): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param class-string<TImplementation> $concrete
	 * @param Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $instance
	 * @param bool $replace
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 */
	public function addTransient(string $service, string $concrete, Closure $factory, ?object $instance = null, bool $replace = false): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $instance
	 * @param bool $replace
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function addScoped(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null, bool $replace = false): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param TImplementation|null $instance
	 * @param bool $replace
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function addSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null, bool $replace = false): self;

	public function endScope(): void;

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
	 * @return callable(): TService
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function requireServiceLate(string $serviceName): callable;

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
	public function setAlias(string $alias, string $serviceName): self;

	/**
	 * @template TService of object
	 *
	 * @param string $alias
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function getByAlias(string $alias): ?object;

	/**
	 * @template TService of object
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
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function hasAlias(string $alias): bool;

	/**
	 * @param string $id
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function has(string $id): bool;

	/**
	 * @template TService of object
	 *
	 * @param string $id
	 *
	 * @return TService|null
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function get(string $id): ?object;

	/**
	 * @template TService of object
	 *
	 * @param string $aliasOrServiceName
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no service with the given alias exists
	 * @throws InvalidArgumentException if the alias or service name is empty
	 */
	public function require(string $aliasOrServiceName): object;

	/**
	 * @template TService of object
	 *
	 * @param string $aliasOrServiceName
	 *
	 * @return callable(): TService
	 *
	 * @throws InvalidArgumentException if the alias or service name is empty
	 */
	public function requireLate(string $aliasOrServiceName): callable;

	/**
	 * @param string $serviceName
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function removeService(string $serviceName): self;

	/**
	 * @param string $alias
	 *
	 * @throws InvalidArgumentException if the alias is empty
	 */
	public function removeAlias(string $alias): self;

	/**
	 * @param string $aliasOrServiceName
	 *
	 * @throws InvalidArgumentException if the alias or service name is empty
	 */
	public function remove(string $aliasOrServiceName): self;
}
