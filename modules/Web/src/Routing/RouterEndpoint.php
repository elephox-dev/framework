<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\AmbiguousMatchException;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyValuePair;
use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\EmptySequenceException;
use Elephox\DI\Contract\Resolver;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\OOR\Regex;
use Elephox\Web\Contract\PipelineEndpoint;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\Router;

readonly class RouterEndpoint implements PipelineEndpoint
{
	public function __construct(
		private Router $router,
		private Resolver $resolver,
	) {
	}

	public function handle(Request $request): ResponseBuilder
	{
		$method = $request->getMethod();
		$path = $request->getUrl()->getPath();

		/** @var ArrayList<GenericKeyValuePair<RouteData, RouteParametersMap>> $matching */
		$matching = $this->router->getMatching($method, $path)->toArrayList();

		try {
			/**
			 * @psalm-suppress MixedAssignment
			 * @psalm-suppress MixedArrayAccess
			 *
			 * @var RouteData $route
			 * @var RouteParametersMap $params
			 */
			[$route, $params] = $matching->single();
		} catch (AmbiguousMatchException) {
			/** @var Grouping<float, RouteData, RouteParametersMap> $orderedBySpecificity */
			$orderedBySpecificity = $matching
				->groupBy(static fn (GenericKeyValuePair $kvp) => Regex::specificity($kvp->getKey()->getRegExp(), $path))
				->orderByDescending(static fn (Grouping $g) => $g->groupKey())
				->first()
			;

			try {
				/**
				 * @var RouteData $route
				 * @var RouteParametersMap $params
				 */
				[$route, $params] = $orderedBySpecificity->single();
			} catch (AmbiguousMatchException $ambiguous) {
				throw new AmbiguousRouteException("Router matched more than one route for path '$path'", previous: $ambiguous);
			}
		} catch (EmptySequenceException $empty) {
			throw new NoRouteFoundException("Router matched no route for path '$path'", previous: $empty);
		}

		/** @var Closure(mixed): ResponseBuilder $handler */
		$handler = $route->getHandler();

		/** @var ResponseBuilder */
		return $this->resolver->call($handler, overrideArguments: ['params' => $params]);
	}
}
