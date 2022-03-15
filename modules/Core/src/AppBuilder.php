<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Contract\App;
use Elephox\Core\Contract\ServiceContainer;
use RuntimeException;

/**
 * @template T of App
 * @template TServiceContainer of \Elephox\Core\Contract\ServiceContainer
 */
abstract class AppBuilder implements Contract\AppBuilder
{
	/**
	 * @param TServiceContainer $services
	 */
	public function __construct(protected ServiceContainer $services)
	{
	}

	public function hasChanged(): bool
	{
		return true;
	}

	/**
	 * @return T
	 */
	public function getCached(): App
	{
		throw new RuntimeException('Cached app not implemented');
	}

	/**
	 * @return TServiceContainer
	 */
	public function getServices(): ServiceContainer
	{
		return $this->services;
	}
}
