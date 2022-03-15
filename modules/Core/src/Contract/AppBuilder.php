<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

use RuntimeException;

/**
 * @template T of App
 * @template TServiceContainer of \Elephox\Core\Contract\ServiceContainer
 *
 * @property-read TServiceContainer $services
 * @property-read T $cached
 */
interface AppBuilder
{
	/**
	 * @return T
	 */
	public function build(): App;

	public function hasChanged(): bool;

	/**
	 * @return T
	 *
	 * @throws RuntimeException if the app has not been built yet
	 */
	public function getCached(): App;

	/**
	 * @return TServiceContainer
	 */
	public function getServices(): ServiceContainer;
}
