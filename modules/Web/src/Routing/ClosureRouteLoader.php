<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Web\Routing\Contract\RouteLoader;

readonly class ClosureRouteLoader implements RouteLoader
{
	public function __construct(
		private RequestMethod|string|iterable $method,
		private string $template,
		private Closure $closure,
	) {
	}

	public function getRoutes(): GenericEnumerable
	{
		return new Enumerable(function () {
			yield new ClosureRouteData(
				$this,
				$this->template,
				[],
				$this->method,
				$this->closure,
			);
		});
	}
}
