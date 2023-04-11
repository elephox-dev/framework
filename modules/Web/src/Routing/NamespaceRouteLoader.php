<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DI\Contract\Resolver;
use Elephox\Web\Routing\Contract\RouteLoader;
use ReflectionException;

readonly class NamespaceRouteLoader implements RouteLoader
{
	public function __construct(
		public string $namespace,
		public Resolver $resolver,
	) {
	}

	public function getRoutes(): GenericEnumerable
	{
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
		$classNames = [];

		NamespaceLoader::iterateNamespace($this->namespace, static function (string $className) use (&$classNames): void {
			$classNames[] = $className;
		});

		foreach ($classNames as $loader) {
			yield new ClassRouteLoader($loader, $this->resolver);
		}
	}
}
