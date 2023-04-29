<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Http\Contract\RequestMethod;

interface RouterBuilder
{
	public function addLoader(RouteLoader $loader): void;

	/**
	 * @param class-string $className
	 */
	public function addRoutesFromClass(string $className): void;

	public function addRoutesFromNamespace(string $namespace): void;

	/**
	 * @param RequestMethod|non-empty-string|iterable<mixed, RequestMethod|non-empty-string> $method
	 */
	public function addRoute(RequestMethod|string|iterable $method, string $template, callable $handler): void;

	/**
	 * @return GenericReadonlyList<RouteLoader>
	 */
	public function getLoaders(): GenericReadonlyList;

	/**
	 * @return iterable<RouteData>
	 */
	public function getRoutes(): iterable;

	public function build(): Router;
}
