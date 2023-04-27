<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericKeyValuePair;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Web\Routing\RouteParametersMap;

interface Router
{
	public function addLoader(RouteLoader $loader): void;

	public function clearRoutes(): void;

	public function loadRoutes(): void;

	/**
	 * @return GenericReadonlyList<RouteData>
	 */
	public function getLoadedRoutes(): GenericReadonlyList;

	/**
	 * @return GenericEnumerable<GenericKeyValuePair<RouteData, RouteParametersMap>>
	 */
	public function getMatching(string $method, string $path): GenericEnumerable;
}
