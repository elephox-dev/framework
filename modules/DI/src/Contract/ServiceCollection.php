<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InvalidServiceDescriptorException;
use Elephox\DI\ServiceDescriptor;
use InvalidArgumentException;

interface ServiceCollection extends ServiceProvider
{
	public function add(ServiceDescriptor $descriptor): self;

	public function tryAdd(ServiceDescriptor $descriptor): self;

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $service
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function remove(string $service): self;

	public function removeAll(): self;

	public function buildProvider(): RootServiceProvider;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param object|null $instance
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function addSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param object|null $instance
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function tryAddSingleton(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param object|null $instance
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 */
	public function addTransient(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param object|null $instance
	 *
	 * @throws InvalidArgumentException if the service name or the implementation name is empty
	 */
	public function tryAddTransient(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param object|null $instance
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function addScoped(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self;

	/**
	 * @template TService of object
	 * @template TImplementation of object
	 *
	 * @param class-string<TService> $service
	 * @param null|class-string<TImplementation> $concrete
	 * @param null|Closure(mixed): TImplementation $factory
	 * @param object|null $instance
	 *
	 * @throws InvalidArgumentException if the service name is empty or no implementation and not name is provided
	 * @throws InvalidServiceDescriptorException if neither the implementation factory nor the implementation is provided
	 */
	public function tryAddScoped(string $service, ?string $concrete = null, ?Closure $factory = null, ?object $instance = null): self;
}
