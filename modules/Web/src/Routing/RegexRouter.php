<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Collection\KeyedEnumerable;
use Elephox\Collection\ObjectSet;
use Elephox\OOR\Regex;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\Router;

readonly class RegexRouter implements Router
{
	/**
	 * @var ObjectSet<RouteLoader> $loaders
	 */
	private ObjectSet $loaders;

	/**
	 * @var ArrayList<RouteData> $routes
	 */
	private ArrayList $routes;

	public function __construct()
	{
		/** @var ObjectSet<RouteLoader> */
		$this->loaders = new ObjectSet();

		/** @var ArrayList<RouteData> */
		$this->routes = new ArrayList();
	}

	public function addLoader(RouteLoader $loader): void
	{
		$this->loaders->add($loader);
	}

	public function clearRoutes(): void
	{
		$this->routes->clear();
	}

	public function loadRoutes(): void
	{
		/** @var RouteLoader $loader */
		foreach ($this->loaders as $loader) {
			$this->routes->addAll($loader->getRoutes());
		}
	}

	public function getLoadedRoutes(): GenericReadonlyList
	{
		return $this->routes;
	}

	public function getMatching(string $method, string $path): GenericKeyedEnumerable
	{
		if ($this->routes->isEmpty()) {
			$this->loadRoutes();
		}

		return new KeyedEnumerable(function () use ($method, $path) {
			/** @var RouteData $routeData */
			foreach ($this->routes as $routeData) {
				if (!$routeData->getMethods()->contains($method)) {
					continue;
				}

				$regex = $routeData->getRegExp();
				$matches = Regex::match($regex, $path);

				if ($matches !== null) {
					$namedParams = new RouteParametersMap(
						$matches
							->whereKey(static fn (int|string $k): bool => is_string($k))
							->toArray(),
					);

					yield $routeData => $namedParams;
				}
			}
		});
	}
}
