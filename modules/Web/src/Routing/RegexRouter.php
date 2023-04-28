<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericKeyValuePair;
use Elephox\Collection\Enumerable;
use Elephox\Collection\KeyValuePair;
use Elephox\OOR\Regex;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\Router;

readonly class RegexRouter implements Router
{
	/**
	 * @param GenericEnumerable<RouteData> $routes
	 */
	public function __construct(private GenericEnumerable $routes)
	{
	}

	public function getRoutes(): GenericEnumerable
	{
		return $this->routes;
	}

	public function getMatching(string $method, string $path): GenericEnumerable
	{
		/** @var Enumerable<GenericKeyValuePair<RouteData, RouteParametersMap>> */
		return new Enumerable(function () use ($method, $path) {
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

					yield new KeyValuePair($routeData, $namedParams);
				}
			}
		});
	}
}
