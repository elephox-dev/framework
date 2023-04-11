<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface RouteLoader
{
	/**
	 * @return GenericEnumerable<RouteData>
	 */
	public function getRoutes(): GenericEnumerable;
}
