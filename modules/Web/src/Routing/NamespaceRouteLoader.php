<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Autoloading\Composer\GlobalNamespaceLoader;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\RouteLoader;
use ReflectionException;

readonly class NamespaceRouteLoader implements RouteLoader
{
	public function __construct(public string $namespace)
	{
	}

	public function getRoutes(): GenericEnumerable
	{
		/** @var Enumerable<RouteData> */
		return new Enumerable(function () {
			foreach ($this->getClassRouteLoaders() as $loader) {
				yield from $loader->getRoutes();
			}
		});
	}

	/**
	 * @return iterable<ClassRouteLoader>
	 *
	 * @throws ReflectionException
	 */
	public function getClassRouteLoaders(): iterable
	{
		foreach (GlobalNamespaceLoader::iterateNamespace($this->namespace) as $loader) {
			yield new ClassRouteLoader($loader);
		}
	}
}
