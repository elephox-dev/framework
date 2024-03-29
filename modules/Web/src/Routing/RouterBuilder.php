<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArraySet;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\Router;
use Elephox\Web\Routing\Contract\RouterBuilder as RouterBuilderContract;
use ReflectionException;

readonly class RouterBuilder implements RouterBuilderContract
{
	/**
	 * @var ArrayList<RouteLoader> $loaders
	 */
	private ArrayList $loaders;

	public function __construct()
	{
		/** @var ArrayList<RouteLoader> */
		$this->loaders = new ArrayList();
	}

	public function addLoader(RouteLoader $loader): void
	{
		$this->loaders->add($loader);
	}

	/**
	 * @param class-string $className
	 *
	 * @throws ReflectionException
	 */
	public function addRoutesFromClass(string $className): void
	{
		$loader = new ClassRouteLoader($className);

		$this->addLoader($loader);
	}

	public function addRoutesFromNamespace(string $namespace): void
	{
		$loader = new NamespaceRouteLoader($namespace);

		$this->addLoader($loader);
	}

	/**
	 * @param RequestMethod|non-empty-string|iterable<mixed, RequestMethod|non-empty-string> $method
	 */
	public function addRoute(RequestMethod|string|iterable $method, string $template, callable $handler): void
	{
		$loader = new ClosureRouteLoader($method, $template, $handler(...));

		$this->addLoader($loader);
	}

	public function getLoaders(): GenericReadonlyList
	{
		return $this->loaders;
	}

	public function getRoutes(): iterable
	{
		/** @var RouteLoader $loader */
		foreach ($this->loaders as $loader) {
			yield from $loader->getRoutes();
		}
	}

	public function build(): Router
	{
		/** @var ArraySet<RouteData> $routes */
		$routes = new ArraySet();
		$routes->addAll($this->getRoutes());

		return new RegexRouter($routes);
	}
}
