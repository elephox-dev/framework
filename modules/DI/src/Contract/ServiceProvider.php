<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Elephox\DI\ServiceInstantiationException;
use Elephox\DI\ServiceNotFoundException;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

interface ServiceProvider extends ContainerInterface, Resolver
{
	/**
	 * @param string $id
	 *
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function has(string $id): bool;

	/**
	 * @template TService of object
	 *
	 * @param string|class-string<TService> $id
	 *
	 * @return TService
	 *
	 * @throws ServiceNotFoundException if no such service is registered
	 * @throws ServiceInstantiationException if the service cannot be instantiated
	 * @throws InvalidArgumentException if the service name is empty
	 */
	public function get(string $id): object;

	public function dispose(): void;
}
