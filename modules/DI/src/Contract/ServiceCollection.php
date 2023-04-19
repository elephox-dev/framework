<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InvalidServiceDescriptorException;
use Elephox\DI\ServiceInstantiationException;
use Elephox\DI\ServiceLifetime;
use Elephox\DI\ServiceNotFoundException;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
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
	 * @param class-string<TService> $id
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function has(string $id): bool;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $id
	 *
	 * @return TService|null
	 */
	public function get(string $id): ?object;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $service
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no such service is registered
	 * @throws ServiceInstantiationException if the service cannot be instantiated
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function require(string $service): object;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $service
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function remove(string $service): self;
}
