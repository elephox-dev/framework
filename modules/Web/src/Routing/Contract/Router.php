<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericKeyValuePair;
use Elephox\Web\Routing\RouteParametersMap;

interface Router
{
	/**
	 * @return GenericEnumerable<RouteData>
	 */
	public function getRoutes(): GenericEnumerable;

	/**
	 * @return GenericEnumerable<GenericKeyValuePair<RouteData, RouteParametersMap>>
	 */
	public function getMatching(string $method, string $path): GenericEnumerable;
}
