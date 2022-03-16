<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Elephox\DI\ServiceNotFoundException;

interface ServiceProvider
{
	/**
	 * @template TService
	 *
	 * @param class-string<TService> $serviceName
	 * @return TService|null
	 */
	public function getService(string $serviceName): ?object;

	/**
	 * @template TService
	 *
	 * @param class-string<TService> $serviceName
	 * @return TService
	 *
	 * @throws ServiceNotFoundException
	 */
	public function requireService(string $serviceName): object;
}
